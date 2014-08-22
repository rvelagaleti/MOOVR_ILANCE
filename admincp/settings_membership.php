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

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=subscriptions', $_SESSION['ilancedata']['user']['slng']);
    
// #### MIGRATE SUBSCRIPTION PLAN USERS TO ANOTHER PLAN ################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_migrate-subscription-users' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$area_title = '{_migrating_customers}';
	$page_title = SITE_NAME . ' - {_migrating_customers}';

	$subscriptionid = intval($ilance->GPC['id']);
	$subscription_group_name = $ilance->db->fetch_field(DB_PREFIX . 'subscription', 'subscriptionid=' . intval($ilance->GPC['id']), 'title_' . $_SESSION['ilancedata']['user']['slng']);
	$subscription_duration = $ilance->db->fetch_field(DB_PREFIX . 'subscription', 'subscriptionid=' . intval($ilance->GPC['id']), 'length');
	$subscription_units = $ilance->db->fetch_field(DB_PREFIX . 'subscription', 'subscriptionid=' . intval($ilance->GPC['id']), 'units');
	$migrate_plan_pulldown = $ilance->admincp->print_migrate_to_pulldown($ilance->GPC['id']);
	$count = '0';				    
	$sqla = $ilance->db->query("
		SELECT COUNT(*) AS usersactive
		FROM " . DB_PREFIX . "subscription_user
		WHERE subscriptionid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sqla) > 0)
	{
		$resactive = $ilance->db->fetch_array($sqla);
		$count = $resactive['usersactive'];
	}

	$pprint_array = array('subscription_duration','subscription_units','count','subscription_group_name','subscriptionid','migrate_plan_pulldown','input_style');
	
	($apihook = $ilance->api('admincp_subscriptions_migrateplan_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'subscriptions_migrateplan.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### MIGRATE SUBSCRIPTION PLAN USERS HANDLER ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_migrate-subscription-users' AND isset($ilance->GPC['migratefromid']) AND $ilance->GPC['migratefromid'] > 0 AND isset($ilance->GPC['migratetoid']) AND $ilance->GPC['migratetoid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_migrating_customers}';
	$page_title = SITE_NAME . ' - {_migrating_customers}';

	$ilance->db->query("
		UPDATE " . DB_PREFIX . "subscription_user
		SET subscriptionid = '" . intval($ilance->GPC['migratetoid']) . "'
		WHERE subscriptionid = '" . intval($ilance->GPC['migratefromid']) . "'
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
	exit();
}

// #### ADD NEW SUBSCRIPTION GROUP #####################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-subscription-group')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_composing_new_subscription_permission_group}';
	$page_title = SITE_NAME . ' - {_composing_new_subscription_permission_group}';
	$title = $field1 = $field2 = '';
	$sql_lang = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language");
	while($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
	{
		$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
		if (!isset($ilance->GPC['description_' . $languagecode]) OR empty($ilance->GPC['description_' . $languagecode]) OR !isset($ilance->GPC['title_' . $languagecode]) OR empty($ilance->GPC['title_' . $languagecode]) )
		{
			print_action_failed('{_you_can_only_create_a_new_subscription_permission_group_by_filling_out}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		$title .= empty($title) ? "title_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "'" : " OR title_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "'";
		$field1 .= 'title_' . $languagecode . ', description_' . $languagecode . ', ';
		$field2 .= "
		'" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "',
		'" . $ilance->db->escape_string($ilance->GPC['description_' . $languagecode]) . "',";
	}
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "subscription_group
		WHERE $title
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		print_action_failed('{_this_subscription_permission_group_already_exists_and_cannot_be_recreated}', $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
	else
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "subscription_group
			(subscriptiongroupid, " . $field1 . "canremove)
			VALUES(
			NULL,
			" . $field2 . "
			'1')
		");
		
		print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
}

// #### ADD NEW SUBSCRIPTION PLAN ######################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-subscription-plan')
{
	$area_title = '{_composing_new_subscription_plan}';
	$page_title = SITE_NAME . ' - {_composing_new_subscription_plan}';
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->GPC['icon'] = isset($ilance->GPC['icon']) ? $ilance->GPC['icon'] : 'default.gif';
	$migratetoid = 0;
	$migratelogic = 'none';
	if (isset($ilance->GPC['migratetoid']))
	{
		if ($ilance->GPC['migratetoid'] != 'none')
		{
			$migratetoid = intval($ilance->GPC['migratetoid']);
		}
		if ($ilance->GPC['migratelogic'] != 'none')
		{
			$migratelogic = $ilance->GPC['migratelogic'];
		}
	}
	
	$title = $field1 = $field2 = '';
	$sql_lang = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language");
	while($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
	{
		$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
		if (!isset($ilance->GPC['description_' . $languagecode]) OR empty($ilance->GPC['description_' . $languagecode]) OR !isset($ilance->GPC['title_' . $languagecode]) OR empty($ilance->GPC['title_' . $languagecode]) )
		{
			print_action_failed('{_you_can_only_create_a_new_subscription_plan_by_filling_out}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		$title .= empty($title) ? "title_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "'" : " OR title_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "'";
		$field1 .= 'title_' . $languagecode . ', description_' . $languagecode . ', ';
		$field2 .= "
		'" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "',
		'" . $ilance->db->escape_string($ilance->GPC['description_' . $languagecode]) . "',";
	}
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "subscription
		WHERE $title
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		print_action_failed('{_this_subscription_plan_already_exists_and_cannot_be_recreated}', $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
	else
	{
		if (empty($ilance->GPC['cost']))
		{
			print_action_failed('{_you_can_only_create_a_new_subscription_plan_by_filling_out}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		elseif($ilance->GPC['units'] == 'Y' AND intval($ilance->GPC['duration']) > 10)
		{
			print_action_failed('{_maximum_length_of_subscription_plan_that_you_can_create_is_10_years}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
			
		if ($ilance->GPC['visible'] == '1')
		{
			$visible_registration = '1';
			$visible_upgrade = '0';
		}
		else if ($ilance->GPC['visible'] == '2')
		{
			$visible_registration = '0';
			$visible_upgrade = '1';
		}
		else if ($ilance->GPC['visible'] == '3')
		{
			$visible_registration = '1';
			$visible_upgrade = '1';
		}
		else 
		{
			$visible_registration = '0';
			$visible_upgrade = '0';
		}
		$sort =  isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : 0;
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "subscription
			(subscriptionid, " . $field1 . "cost, length, units, subscriptiongroupid, roleid, active, visible_registration, visible_upgrade, icon, sort, migrateto, migratelogic)
			VALUES(
			NULL,
			" . $field2 . "
			'" . $ilance->db->escape_string($ilance->GPC['cost']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['duration']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['units']) . "',
			'" . intval($ilance->GPC['subscriptiongroupid']) . "',
			'" . intval($ilance->GPC['roleid']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['active']) . "',
			'" . intval($visible_registration) . "',
			'" . intval($visible_upgrade) . "',
			'" . $ilance->db->escape_string($ilance->GPC['icon']) . "',
			'" . intval($sort) . "',
			'" . $migratetoid . "',
			'" . $ilance->db->escape_string($migratelogic) . "')
		");
		print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
}

// #### ADD SUBSCRIPTION ROLE ##########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-role')
{
	$area_title = '{_composing_new_subscription_role}';
	$page_title = SITE_NAME . ' - {_composing_new_subscription_role}';
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$field1 = $field2 = '';
	$sql_lang = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language");
	while($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
	{
		$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
		if (!isset($ilance->GPC['title_' . $languagecode]) OR empty($ilance->GPC['title_' . $languagecode]) )
		{
			print_action_failed('{_you_can_only_create_a_new_subscription_role_by_filling_out_all}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		$field1 .= 'purpose_' . $languagecode . ', title_' . $languagecode . ', ';
		$field2 .= "
		'" . $ilance->db->escape_string($ilance->GPC['purpose_' . $languagecode]) . "',
		'" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "',";
	}
	
	$ilance->GPC['roletype'] = isset($ilance->GPC['roletype']) ? $ilance->db->escape_string($ilance->GPC['roletype']) : '';
	$ilance->GPC['roleusertype'] = isset($ilance->GPC['roleusertype']) ? $ilance->db->escape_string($ilance->GPC['roleusertype']) : '';

	// create new subscription commission group
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "subscription_roles
		(roleid, " . $field1 . "custom, roletype, roleusertype, active)
		VALUES(
		NULL,
		" . $field2 . "
		'" . $ilance->db->escape_string($ilance->GPC['custom']) . "',
		'" . $ilance->GPC['roletype'] . "',
		'" . $ilance->GPC['roleusertype'] . "',
		'" . intval($ilance->GPC['visible']) . "')
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
	exit();
}

// #### REMOVE SUBSCRIPTION ROLE #######################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-role' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_removing_subscription_roles}';
	$page_title = SITE_NAME . ' - {_removing_subscription_roles}';
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "subscription_roles
		WHERE roleid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
	exit();
}

// #### REMOVE SUBSCRIPTION PLAN #######################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-subscription-plan' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_removing_subscription_plans}';
	$page_title = SITE_NAME . ' - {_removing_subscription_plans}';
	$sql1 = $ilance->db->query("
		SELECT subscriptionid
		FROM " . DB_PREFIX . "subscription
		WHERE subscriptiongroupid != '" . intval($ilance->GPC['id']) . "'
			AND visible_upgrade = '1'
		LIMIT 1
	");
	$sql2 = $ilance->db->query("
		SELECT subscriptionid
		FROM " . DB_PREFIX . "subscription
		WHERE subscriptiongroupid != '" . intval($ilance->GPC['id']) . "'
			AND visible_registration = '1'
		LIMIT 1
	");
	$num1 = $ilance->db->num_rows($sql1);
	$num2 = $ilance->db->num_rows($sql2);
	if ($num1 > 0 AND $num2 > 0)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "subscription
			WHERE subscriptionid = '" . intval($ilance->GPC['id']) . "'
			    AND canremove = '1'
			LIMIT 1
		");
	}
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
	exit();
}

// #### REMOVE SUBSCRIPTION GROUP ######################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-subscription-group' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_removing_subscription_groups}';
	$page_title = SITE_NAME . ' - {_removing_subscription_groups}';
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "subscription_group
		WHERE subscriptiongroupid = '" . intval($ilance->GPC['id']) . "'
	");
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "subscription_permissions
		WHERE subscriptiongroupid = '" . intval($ilance->GPC['id']) . "'
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
	exit();
}

// #### ADD NEW SUBSCRIPTION PERMISSIONS ###############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-permissions' AND isset($ilance->GPC['accesstext']) AND !empty($ilance->GPC['accesstext']) AND isset($ilance->GPC['accessname']) AND !empty($ilance->GPC['accessname']) AND isset($ilance->GPC['accesstype']) AND !empty($ilance->GPC['accesstype']) AND isset($ilance->GPC['value']) AND !empty($ilance->GPC['value']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_composing_subscription_permissions}';
	$page_title = SITE_NAME . ' - {_composing_subscription_permissions}';
	if ($ilance->subscription_plan->add_subscription_permissions($ilance->GPC['accesstext'], $ilance->GPC['accessdescription'], $ilance->GPC['accessname'], $ilance->GPC['accesstype'], $ilance->GPC['value'], 1))
	{
		print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
		exit();    
	}
	else
	{
		print_action_failed('{_this_permission_access_name_has_already_been_defined_and_cannot_be_recreated}', $ilpage['settings'] . '?cmd=subscriptions');
		exit();    
	}
}

// #### UPDATE SUBSCRIPTION PERMISSIONS ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_change-permissions')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	// was submit button to update permissions called?
	if (isset($ilance->GPC['updatepermissions']))
	{
		$area_title = '{_updating_subscription_permissions}';
		$page_title = SITE_NAME . ' - {_updating_subscription_permissions}';
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "subscription_permissions
			WHERE subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			foreach ($ilance->GPC AS $k => $v)
			{
				if ($k != 'cmd' OR $k != 'subcmd' OR $k != 'subscriptiongroupid' OR $k != 'updatepermissions')
				{
					$vis = 0;
					if (isset($ilance->GPC['accessvisible'][$k]) AND $ilance->GPC['accessvisible'][$k] == 'on')
					{
						$vis = 1;
					}
					if (isset($v) AND !is_array($v))
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "subscription_permissions
							SET value = '" . $ilance->db->escape_string($v) . "',
							visible = '" . intval($vis) . "'
							WHERE accessname = '" . $ilance->db->escape_string($k) . "'
							    AND subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "'
							LIMIT 1
						");
					}
				}
			}
			$notice = '{_access_permissions_have_been_updated_for_subscription_group_id}' . " <strong>" . intval($ilance->GPC['subscriptiongroupid']) . "</strong>.";
		}
		
		// #### CREATE NEW SUBSCRIPTION PERMISSIONS ####
		else
		{
			// create an entirely new set of subscription permissions for this new group
			// which we'll be using an existing permissions group as a base start to go on

			// fetch permissions (original and new custom ones added by the admin
			$sql3 = $ilance->db->query("
				SELECT id, subscriptiongroupid, accessname, accesstype, value, original, iscustom
				FROM " . DB_PREFIX . "subscription_permissions
				WHERE original = '1' OR iscustom = '1'
				GROUP BY accessname
				ORDER BY id ASC
			");
			if ($ilance->db->num_rows($sql3) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql3))
				{
					// create new permissions
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "subscription_permissions
						(id, subscriptiongroupid, accessname, accesstype, value, canremove, original, iscustom)
						VALUES(
						NULL,
						'" . intval($ilance->GPC['subscriptiongroupid']) . "',
						'" . $ilance->db->escape_string($res['accessname']) . "',
						'" . $ilance->db->escape_string($res['accesstype']) . "',
						'" . $ilance->db->escape_string($res['value']) . "',
						'1',
						'" . $res['original'] . "',
						'" . $res['iscustom'] . "'
						)
					");
				}
	
				// finally update new permissions with any pre-configured settings the admin may have enabled/disabled for this subscription group
				foreach ($_POST AS $k => $v)
				{
					if ($k != 'cmd' AND $k != 'subscriptiongroupid' AND $k != 'updatepermissions')
					{
						if (isset($v) AND !is_array($v))
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "subscription_permissions
								SET value = '" . $ilance->db->escape_string($v) . "'
								WHERE accessname = '" . $ilance->db->escape_string($k) . "'
								    AND subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "'
								LIMIT 1
							");
						}
					}
				}
			}
		}
		print_action_success('{_access_permissions_have_been_updated_for_subscription_group_id}' . " <strong>" . intval($ilance->GPC['subscriptiongroupid']) . "</strong>.", $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
	
	// delete subscription permission
	else if (isset($ilance->GPC['deletepermissions']))
	{
		$notice = '';
		foreach ($ilance->GPC['accessname'] AS $varname)
		{
			if (!empty($varname))
			{
				$sql = $ilance->db->query("
					SELECT canremove
					FROM " . DB_PREFIX . "subscription_permissions
					WHERE accessname = '" . $ilance->db->escape_string($varname) . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql);
					if ($res['canremove'])
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "subscription_permissions
							WHERE accessname = '" . $ilance->db->escape_string($varname) . "'
						");
						$notice .= '{_access_permission_removed}' . " <strong>{$varname}</strong>";
					}
					else
					{
						$notice .= "<strong>{$varname}</strong> " . '{_is_a_framework_dependent_permission_resource_and_cannot_be_removed}';
					}
				}
			}		
		}
		if (empty($notice))
		{
			$success = false;
			print_action_failed('{_warning_no_access_permissions_were_deleted_to_delete_a_permission_you_must_select_it_first_by_using_the_checkbox_option_beside_each_item_you_wish_to_remove}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		else
		{
			print_action_success($notice, $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}	
	}
}
	
// #### EDIT SUBSCRIPTION PLAN #########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-subscription-plan' AND $ilance->GPC['id'] > 0)
{
	$area_title = '{_updating_subscription_plan}';
	$page_title = SITE_NAME . ' - {_updating_subscription_plan}';
		
	$id = intval($ilance->GPC['id']);
	$subscriptionid = $id;
	
	$migrate_plan_pulldown = $ilance->admincp->print_migrate_to_pulldown($ilance->GPC['id']);
	$migrate_billing_pulldown = $ilance->admincp->print_migrate_billing_pulldown($ilance->GPC['id']);
	
	$sqlsubscriptions = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "subscription
		WHERE subscriptionid = '" . $id . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sqlsubscriptions) > 0)
	{
		$res = $ilance->db->fetch_array($sqlsubscriptions);
		$subscriptiongroupid = $res['subscriptiongroupid'];
		$sort = $res['sort'];
		$languages = array();
		$sql_lang = $ilance->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
		while($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
		{
			$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
			
			$language['title'] = stripslashes($res['title_' . $languagecode]);
			$language['description'] = stripslashes($res['description_' . $languagecode]);
			$language['language'] = $res_lang['title'];
			$language['languagecode'] = $languagecode;
			
			$languages[] = $language;
		}
		$cost = $res['cost'];
		$icon = $res['icon'];
		$roleid	= $res['roleid'];
		$duration_pulldown = '<select name="duration" class="select-75"><option value="1"'; if ($res['length'] == "1") {
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>1</option>';
		$duration_pulldown .= '<option value="2"'; if ($res['length'] == "2") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>2</option>';
		$duration_pulldown .= '<option value="3"'; if ($res['length'] == "3") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>3</option>';
		$duration_pulldown .= '<option value="4"'; if ($res['length'] == "4") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>4</option>';
		$duration_pulldown .= '<option value="5"'; if ($res['length'] == "5") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>5</option>';
		$duration_pulldown .= '<option value="6"'; if ($res['length'] == "6") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>6</option>';
		$duration_pulldown .= '<option value="7"'; if ($res['length'] == "7") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>7</option>';
		$duration_pulldown .= '<option value="8"'; if ($res['length'] == "8") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>8</option>';
		$duration_pulldown .= '<option value="9"'; if ($res['length'] == "9") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>9</option>';
		$duration_pulldown .= '<option value="10"'; if ($res['length'] == "10") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>10</option>';
		$duration_pulldown .= '<option value="11"'; if ($res['length'] == "11") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>11</option>';
		$duration_pulldown .= '<option value="12"'; if ($res['length'] == "12") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>12</option>';
		$duration_pulldown .= '<option value="13"'; if ($res['length'] == "13") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>13</option>';
		$duration_pulldown .= '<option value="14"'; if ($res['length'] == "14") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>14</option>';
		$duration_pulldown .= '<option value="15"'; if ($res['length'] == "15") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>15</option>';
		$duration_pulldown .= '<option value="16"'; if ($res['length'] == "16") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>16</option>';
		$duration_pulldown .= '<option value="17"'; if ($res['length'] == "17") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>17</option>';
		$duration_pulldown .= '<option value="18"'; if ($res['length'] == "18") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>18</option>';
		$duration_pulldown .= '<option value="19"'; if ($res['length'] == "19") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>19</option>';
		$duration_pulldown .= '<option value="20"'; if ($res['length'] == "20") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>20</option>';
		$duration_pulldown .= '<option value="21"'; if ($res['length'] == "21") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>21</option>';
		$duration_pulldown .= '<option value="22"'; if ($res['length'] == "22") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>22</option>';
		$duration_pulldown .= '<option value="23"'; if ($res['length'] == "23") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>23</option>';
		$duration_pulldown .= '<option value="24"'; if ($res['length'] == "24") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>24</option>';
		$duration_pulldown .= '<option value="25"'; if ($res['length'] == "25") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>25</option>';
		$duration_pulldown .= '<option value="26"'; if ($res['length'] == "26") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>26</option>';
		$duration_pulldown .= '<option value="27"'; if ($res['length'] == "27") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>27</option>';
		$duration_pulldown .= '<option value="28"'; if ($res['length'] == "28") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>28</option>';
		$duration_pulldown .= '<option value="29"'; if ($res['length'] == "29") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>29</option>';
		$duration_pulldown .= '<option value="30"'; if ($res['length'] == "30") { 
		$duration_pulldown .= ' selected="selected" '; }
		$duration_pulldown .= '>30</option>';
		$duration_pulldown .= '</select>';
		$units_pulldown = '<select name="units" class="select-75"><option value="D"'; if ($res['units'] == "D") { 
		$units_pulldown .= ' selected="selected"'; }
		$units_pulldown .= '>'.'{_days}'.'</option>';
		$units_pulldown .= '<option value="M"'; if ($res['units'] == "M") { 
		$units_pulldown .= ' selected="selected"'; }
		$units_pulldown .= '>'.'{_months}'.'</option>'; 
		$units_pulldown .= '<option value="Y"'; if ($res['units'] == "Y") { 
		$units_pulldown .= ' selected="selected"'; }
		$units_pulldown .= '>'.'{_years}'.'</option>';
		$units_pulldown .= '</select>';
		$sqla = $ilance->db->query("
			SELECT *, COUNT(*) AS usercount
			FROM " . DB_PREFIX . "subscription_user
			WHERE subscriptionid = '" . $id . "'
			GROUP BY user_id
		");
		if ($ilance->db->num_rows($sqla) > 0)
		{
			$resactive = $ilance->db->fetch_array($sqla);
			$usercount = intval($resactive['usercount']);
		}
		else
		{
			$usercount = 0;
		}
		$selectoption = '';
		$active_pulldown = '<select name="active" ' . $selectoption . ' class="select-75" onchange="return alert_js(\'Warning: changing the state of this plan will affect ' . $usercount . ' customers within this plan.  When disabled, this plan is no longer available for usage until it becomes re-activated.\')"><option value="yes"'; if ($res['active'] == "yes") {
		$active_pulldown .= ' selected="selected"'; }
		$active_pulldown .= '>{_yes}</option><option value="no"'; if ($res['active'] == "no") {
		$active_pulldown .= ' selected="selected"'; }
		$active_pulldown .= '>{_no}</option></select>';
		$sel1 = $sel2 = $sel3 = $sel4 = '';
		if ($res['visible_registration'] == '1' AND $res['visible_upgrade'] == '0')
		{
			$sel1 = 'selected="selected"';
		}
		else if ($res['visible_registration'] == '0' AND $res['visible_upgrade'] == '1')
		{
			$sel2 = 'selected="selected"';
		}
		else if ($res['visible_registration'] == '1' AND $res['visible_upgrade'] == '1')
		{
			$sel3 = 'selected="selected"';
		}
		else 
		{
			$sel4 = 'selected="selected"';
		}
		
		$visible_pulldown = '<select name="visible" class="select">
<option value="1" ' . $sel1 . '>{_registration}</option>
<option value="2" ' . $sel2 . '>{_upgrade}</option>
<option value="3" ' . $sel3 . '>{_all}</option>
<option value="4" ' . $sel4 . '>{_none}</option>
</select>';
		$permission_group_pulldown = '<select name="subscriptiongroupid" class="select-250"><optgroup label="{_subscription_permissions_resource}">';
		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';  
		$sql_permgroups = $ilance->db->query("
			SELECT subscriptiongroupid, canremove, title_$slng as title, description_$slng as description
			FROM " . DB_PREFIX . "subscription_group
		");
		while ($res_phrasegroups = $ilance->db->fetch_array($sql_permgroups))
		{
			$permission_group_pulldown .= '<option value="' . $res_phrasegroups['subscriptiongroupid'] . '"';
			if ($subscriptiongroupid == $res_phrasegroups['subscriptiongroupid'])
			{
				$permission_group_pulldown .= ' selected="selected"';
			}						    
			$permission_group_pulldown .= '>' . stripslashes($res_phrasegroups['title']) . '</option>';
		}
		$permission_group_pulldown .= '</optgroup></select>';
		$currency = print_left_currency_symbol();
	}
	
	$roleselected = isset($roleid) ? intval($roleid) : '';
	$role_pulldown = $ilance->subscription_role->print_role_pulldown($roleselected, 1, 1, 1);
	$pprint_array = array('migrate_billing_pulldown','migrate_plan_pulldown','role_pulldown','subscriptionid','commission_group_pulldown','permission_group_pulldown','currency','units_pulldown','duration_pulldown','cost','title','description','active_pulldown','visible_pulldown','icon','sort');
	
	($apihook = $ilance->api('admincp_subscriptions_editplan_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'subscriptions_editplan.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'languages');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
	
// #### EDIT SUBSCRIPTION PERMISSION GROUP INFO ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-subscription-group' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$id = intval($ilance->GPC['id']);
	$subscriptiongroupid = $id;
	$show['deletebutton'] = 0;
	
	$area_title = '{_updating_subscription_group}';
	$page_title = SITE_NAME . ' - {_updating_subscription_group}';
	
	// fetch subscription group info
	$sqlsubscriptions = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "subscription_group
		WHERE subscriptiongroupid = '" . intval($subscriptiongroupid) . "'
	");
	if ($ilance->db->num_rows($sqlsubscriptions) > 0)
	{
		// permission group title and description
		$res = $ilance->db->fetch_array($sqlsubscriptions);
		$languages_permission_group = array();
		$sql_lang = $ilance->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
		while ($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
		{
			$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
			$language['title'] = stripslashes($res['title_' . $languagecode]);
			$language['description'] = stripslashes($res['description_' . $languagecode]);
			$language['language'] = $res_lang['title'];
			$language['languagecode'] = $languagecode;
			$languages_permission_group[] = $language;
		}	

		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';  
		
		// fetch subscription plans using this permissions group
		$sqlplans = $ilance->db->query("
			SELECT title_$slng as title
			FROM " . DB_PREFIX . "subscription
			WHERE subscriptiongroupid = '" . $subscriptiongroupid . "'
		");
		if ($ilance->db->num_rows($sqlplans) > 0)
		{
			$plans_in_group = '';
			while ($resplans = $ilance->db->fetch_array($sqlplans))
			{
				$plans_in_group .= stripslashes($resplans['title']) . ', ';
			}					
			$plans_in_group = mb_substr($plans_in_group, 0, -2);
		}
		else
		{
			// no plans currently utilizing this permissions group
			$noplans = 1;
			$plans_in_group = '{_no_subscription_plans_currently_utilizing_this_permissions_group}';
		}
	}
		    
	$sqlitems = $ilance->db->query("
		SELECT id, subscriptiongroupid, accessname, accesstype, value, original, visible
		FROM " . DB_PREFIX . "subscription_permissions
		WHERE subscriptiongroupid = '" . intval($subscriptiongroupid) . "'
		GROUP BY accessname
		ORDER BY id ASC
	");
	if ($ilance->db->num_rows($sqlitems) > 0)
	{
		$row_count2 = 0;
		while ($resitems = $ilance->db->fetch_array($sqlitems))
		{
			if ($resitems['value'] == 'yes' OR $resitems['value'] == 'no')
			{ 
				if ($resitems['value'] == 'yes')
				{
					$userinput = '<label for="yes_' . $resitems['id'] . '">{_yes} <input type="radio" name="' . $resitems['accessname'] . '" value="yes" id="yes_' . $resitems['id'] . '" checked="checked" /></label> <label for="no_' . $resitems['id'] . '">{_no} <input type="radio" name="' . $resitems['accessname'] . '" value="no" id="no_' . $resitems['id'] . '" /></label>';
				}
				else
				{
					$userinput = '<label for="yes_' . $resitems['id'] . '">{_yes} <input type="radio" name="' . $resitems['accessname'] . '" id="yes_' . $resitems['id'] . '" value="yes" /></label> <label for="no_' . $resitems['id'] . '">{_no} <input type="radio" name="' . $resitems['accessname'] . '" value="no" id="no_' . $resitems['id'] . '" checked="checked" /></label>';
				}
			}
			else
			{
				$userinput = '<input type="text" name="' . $resitems['accessname'] . '" value="' . $resitems['value'] . '" style="width:75px; text-align: center" class="input" />';
			}
			$resitems['userinput'] = $userinput;
			$resitems['action'] = '<input type="checkbox" name="accessname[]" value="' . $resitems['accessname'] . '" />';
			$resitems['accesstype'] = $resitems['accesstype'];
			$resitems['accessname'] = stripslashes($resitems['accessname']);
			$resitems['accesstext'] = '<span id="edit_input_' . $resitems['id'] . '">{' . stripslashes('_' . $resitems['accessname'] . '_text') . '}</span>';
			$resitems['accessdescription'] = '{' . stripslashes('_' . $resitems['accessname'] . '_desc') . '}';
			if ($resitems['original'])
			{
				$resitems['iscustom'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
			}
			else
			{
				$resitems['iscustom'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			}

			if ($resitems['visible'] == 1) 
			{
				$checked = '" checked="checked"';
				$vis = 1;
			}
			else 
			{
				$checked = '';
				$vis = 0;
			}
			$resitems['visible_perm'] = '<input type="checkbox" name="accessvisible[' . stripslashes($resitems['accessname']) . ']" '. $checked . '/>';
			$row_count2++;
			$resitems['class2'] = ($row_count2 % 2) ? 'alt1' : 'alt2';
			$access_permission_items[] = $resitems;
		}				
		$show['deletebutton'] = 1;
	}
	else
	{
		// this is a entirely new permissions instance.  We must collect all original permissions
		// and any "new" custom permissions the admin/staff may have created in his/her venture
		// for only 1 given permissions group :-).  collect all original and/or custom created ones
		// and group by the unique "accessname" while ordering the sort as ascending
		$sqlitems = $ilance->db->query("
			SELECT id, subscriptiongroupid, accessname, accesstype, value, original
			FROM " . DB_PREFIX . "subscription_permissions
			WHERE original = '1' OR iscustom = '1'
			GROUP BY accessname
			ORDER BY id ASC
		");
		if ($ilance->db->num_rows($sqlitems) > 0)
		{
			$row_count2 = $row_count  = 0;
			while ($resitems = $ilance->db->fetch_array($sqlitems))
			{
				if ($resitems['value'] == 'yes' OR $resitems['value'] == 'no')
				{ 
					if ($resitems['value'] == 'yes')
					{
						$userinput = '<label for="yes_' . $resitems['id'] . '">{_yes} <input type="radio" name="'.$resitems['accessname'].'" value="yes" id="yes_'.$resitems['id'].'" checked="checked" /></label> <label for="no_'.$resitems['id'].'">{_no} <input type="radio" name="'.$resitems['accessname'].'" value="no" id="no_'.$resitems['id'].'" /></label>';
					}
					else
					{
						$userinput = '<label for="yes_' . $resitems['id'] . '">{_yes} <input type="radio" name="'.$resitems['accessname'].'" id="yes_'.$resitems['id'].'" value="yes" /></label> <label for="no_'.$resitems['id'].'">'.'{_no} <input type="radio" name="'.$resitems['accessname'].'" value="no" id="no_'.$resitems['id'].'" checked="checked" /></label>';
					}
				}
				else
				{
					$userinput = '<input type="text" name="' . $resitems['accessname'] . '" value="' . $resitems['value'] . '" style="width:75px; text-align: center" />';
				}
				$resitems['userinput'] = $userinput;
				$resitems['action'] = '<input type="checkbox" name="accessname[]" value="' . $resitems['accessname'] . '" />';
				$resitems['accesstype'] = $resitems['accesstype'];
				$resitems['accessname'] = stripslashes($resitems['accessname']);
				$resitems['accesstext'] = '<span id="edit_input_' . $resitems['id'] . '">' . stripslashes('{_' . $resitems['accessname'] . '_text}') . '</span>';
				$resitems['accessdescription'] = stripslashes('{_' . $resitems['accessname'] . '_desc}');
				$resitems['class2'] = ($row_count2 % 2) ? 'alt1' : 'alt2';
				$resitems['visible_perm'] = '<input type="checkbox" name="accessvisible[' . stripslashes($resitems['accessname']) . ']" value="1" checked="checked" />';
				$row_count2++;
				if ($resitems['original'])
				{
					$resitems['iscustom'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="{_this_subscription_permission_is_original_and_cannot_be_removed_framework_dependent}" />';
				}
				else
				{
					$resitems['iscustom'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="{_this_subscription_permission_is_custom_and_can_be_removed_added_after_install_by_an_admin}" />';
				}
				$access_permission_items[] = $resitems;
			}
		}
	}
	$new_resource_item  = "<tr>";
	$new_resource_item .= "  <td><div><span class=\"gray\">" . '{_title}' . " ({_example}: {_attach_limit})</span></div><div><input type=\"text\" name=\"accesstext\" style=\"width:98%\"></div><div style=\"padding-top:6px\"><span class=\"gray\">" . '{_description}' . " ({_example}: {_defines_the_attach_limit})</span></div><div><input type=\"text\" name=\"accessdescription\" style=\"width:98%\"></div></td>";
	$new_resource_item .= "  <td><div align=\"center\" class=\"smaller\"><select name=\"accesstype\" style=\"font-family: verdana\"><option value=\"yesno\">".'{_yes}'." / ".'{_no}'."</option><option value=\"int\">Integer</option></select></div></td>";
	$new_resource_item .= "  <td><div align=\"center\" class=\"smaller\"><input type=\"text\" name=\"accessname\" style=\"width:75px\"></div></td>";
	$new_resource_item .= "  <td align=\"center\"><input type=\"text\" name=\"value\" style=\"width:55px\"></td>";
	$new_resource_item .= "  <td align=\"center\"><input type=\"submit\" class=\"buttons\" name=\"newaccess\" value=\"".'{_create}'."\" style=\"font-size:15px\"></td>";
	$new_resource_item .= "</tr>";
	
	$pprint_array = array('new_resource_item','subscriptiongroupid','plans_in_group','subscriptionid','commission_group_pulldown','permission_group_pulldown','currency','units_pulldown','duration_pulldown','cost','title','description','active_pulldown','visible_pulldown','icon','input_style');
	
	($apihook = $ilance->api('admincp_subscriptions_editperm_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'subscriptions_editperm.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'access_permission_items');
	$ilance->template->parse_loop('main', 'languages_permission_group');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
	
// #### EDIT SUBSCRIPTION ROLE #################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-role' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$area_title = '{_updating_subscription_role}';
	$page_title = SITE_NAME . ' - {_updating_subscription_role}';
	
	$id = intval($ilance->GPC['id']);
	$sqlcommissions = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "subscription_roles
		WHERE roleid = '" . $id . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sqlcommissions) > 0)
	{
		$res = $ilance->db->fetch_array($sqlcommissions);
		$languages_role = array();
		$sql_lang = $ilance->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
		while($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
		{
			$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
			$language['title'] = stripslashes($res['title_' . $languagecode]);
			$language['purpose'] = stripslashes($res['purpose_' . $languagecode]);
			$language['language'] = $res_lang['title'];
			$language['languagecode'] = $languagecode;
			$languages_role[] = $language;
		}		
		$custom = $res['custom'];
		$roletypepulldown = $ilance->admincp->print_roletype_pulldown($res['roletype']);
		$roleusertypepulldown = $ilance->admincp->print_roleusertype_pulldown($res['roleusertype']);
		$rolevisible = '<select name="visible" class="select-75"><option value="1">{_yes}</option><option value="0" selected="selected">{_no}</option>';
		if ($res['active'] == 1)
		{
			$rolevisible = '<select name="visible" class="select-75"><option value="1" selected="selected">{_yes}</option><option value="0">{_no}</option>';
		}
	}
	
	$pprint_array = array('roletypepulldown','roleusertypepulldown','id','title','description','purpose','custom','rolevisible','input_style');
	
	($apihook = $ilance->api('admincp_subscriptions_editrole_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'subscriptions_editrole.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'languages_role');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
	
// #### UPDATE SUBSCRIPTION ROLE ###############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-role' AND isset($ilance->GPC['roleid']) AND $ilance->GPC['roleid'] > 0)
{
	$area_title = '{_updating_subscription_role}';
	$page_title = SITE_NAME . ' - {_updating_subscription_role}';
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$field = '';
	$sql_lang = $ilance->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
	while($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
	{
		$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
		if (!isset($ilance->GPC['purpose_' . $languagecode]) OR empty($ilance->GPC['purpose_' . $languagecode]) OR !isset($ilance->GPC['title_' . $languagecode]) OR empty($ilance->GPC['title_' . $languagecode]) )
		{
			print_action_failed('{_please_fill_all_fields}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		$field .= "
		title_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "',
		purpose_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['purpose_' . $languagecode]) . "',
		";
	}
	$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : '1';
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "subscription_roles
		SET $field
		custom = '" . $ilance->db->escape_string($ilance->GPC['custom']) . "',
		roletype = '" . $ilance->db->escape_string($ilance->GPC['roletype']) . "',
		roleusertype = '" . $ilance->db->escape_string($ilance->GPC['roleusertype']) . "',
		active = '" . $visible . "'
		WHERE roleid = '" . intval($ilance->GPC['roleid']) . "'
		LIMIT 1
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
	exit();
}
	
// #### UPDATE SUBSCRIPTION PLAN ###############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-subscription-plan' AND isset($ilance->GPC['subscriptionid']) AND $ilance->GPC['subscriptionid'] > 0)
{
	$area_title = '{_updating_subscription_plan}';
	$page_title = SITE_NAME . ' - {_updating_subscription_plan}';
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$field = '';
	$sql_lang = $ilance->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
	while($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
	{
		$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
		if (!isset($ilance->GPC['description_' . $languagecode]) OR empty($ilance->GPC['description_' . $languagecode]) OR !isset($ilance->GPC['title_' . $languagecode]) OR empty($ilance->GPC['title_' . $languagecode]) )
		{
			print_action_failed('{_please_fill_all_fields}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		$field .= "
		title_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "',
		description_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['description_' . $languagecode]) . "',
		";
	}
	$active = isset($ilance->GPC['active']) ? $ilance->GPC['active'] : '1';
	if ($ilance->GPC['visible'] == '1')
	{
		$visible_registration = '1';
		$visible_upgrade = '0';
	}
	else if ($ilance->GPC['visible'] == '2')
	{
		$visible_registration = '0';
		$visible_upgrade = '1';
	}
	else if ($ilance->GPC['visible'] == '3')
	{
		$visible_registration = '1';
		$visible_upgrade = '1';
	}
	else 
	{
		$visible_registration = '0';
		$visible_upgrade = '0';
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "subscription
		SET $field
		cost = '" . $ilance->db->escape_string($ilance->GPC['cost']) . "',
		length = '" . intval($ilance->GPC['duration']) . "',
		units = '" . $ilance->db->escape_string(mb_strtoupper($ilance->GPC['units'])) . "',
		subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "',
		roleid = '" . intval($ilance->GPC['roleid']) . "',
		active = '" . $active . "',
		visible_registration = '" . $visible_registration . "',
		visible_upgrade = '" . $visible_upgrade . "',
		icon = '" . $ilance->db->escape_string($ilance->GPC['icon']) . "',
		sort ='" . intval($ilance->GPC['sort']) . "',
		migrateto = '" . intval($ilance->GPC['migratetoid']) . "',
		migratelogic = '" . $ilance->db->escape_string($ilance->GPC['migratelogic']) . "'
		WHERE subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'
		LIMIT 1
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
	exit();
}
	
// #### UPDATE SUBSCRIPTION GROUP TITLE ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-subscription-group' AND isset($ilance->GPC['subscriptiongroupid']) AND $ilance->GPC['subscriptiongroupid'] > 0)
{
	$area_title = '{_updating_subscription_group}';
	$page_title = SITE_NAME . ' - {_updating_subscription_group}';
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$field = '';
	$sql_lang = $ilance->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
	while($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
	{
		$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
		if (!isset($ilance->GPC['description_' . $languagecode]) OR empty($ilance->GPC['description_' . $languagecode]) OR !isset($ilance->GPC['title_' . $languagecode]) OR empty($ilance->GPC['title_' . $languagecode]) )
		{
			print_action_failed('{_please_fill_all_fields}', $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		$field .= "
		title_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['title_' . $languagecode]) . "',
		description_" . $languagecode . " = '" . $ilance->db->escape_string($ilance->GPC['description_' . $languagecode]) . "',
		";
	}
	$field = rtrim($field);
	$field = substr($field, 0, -1);
	$sql = $ilance->db->query("
		UPDATE " . DB_PREFIX . "subscription_group
		SET $field
		WHERE subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "'
		LIMIT 1
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=subscriptions');
	exit();
}
else
{
	$area_title = '{_subscription_plan_management_menu}';
	$page_title = SITE_NAME . ' - {_subscription_plan_management_menu}';
	
	($apihook = $ilance->api('admincp_subscription_settings')) ? eval($apihook) : false;
	
	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';  
	
	$resplans = $ilance->db->query("
		SELECT
		s.subscriptionid, s.title_$slng as title, s.description_$slng as description, s.cost, s.length, s.units, s.subscriptiongroupid, s.canremove, s.visible_registration, s.visible_upgrade, s.icon, s.active, s.roleid, s.sort, g.subscriptiongroupid
		FROM " . DB_PREFIX . "subscription as s,
		" . DB_PREFIX . "subscription_group as g
		WHERE s.subscriptiongroupid = g.subscriptiongroupid
		ORDER BY sort ASC
	");
	if ($ilance->db->num_rows($resplans) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($resplans, DB_ASSOC))
		{
			$sqla = $ilance->db->query("
				SELECT COUNT(*) AS usersactive
				FROM " . DB_PREFIX . "subscription_user
				WHERE subscriptionid = '" . $row['subscriptionid'] . "'
					AND active = 'yes'
			");
			if ($ilance->db->num_rows($sqla) > 0)
			{
				$resactive = $ilance->db->fetch_array($sqla);
				$row['active'] = $resactive['usersactive'];
			}
			else
			{
				$row['active'] = '0';
			}
			$sqle = $ilance->db->query("
				SELECT COUNT(*) AS usersexpired
				FROM " . DB_PREFIX . "subscription_user
				WHERE subscriptionid = '" . $row['subscriptionid'] . "'
				    AND active = 'no'
			");
			if ($ilance->db->num_rows($sqle) > 0)
			{
				$resexpired = $ilance->db->fetch_array($sqle);
				$row['expired'] = $resexpired['usersexpired'];
			}
			else
			{
				$row['expired'] = '0';
			}
			$row['subscriptionid'] = $row['subscriptionid'];
			$row['subscriptiongroupid'] = $row['subscriptiongroupid'];
			$row['subscriptiongroupname'] = $ilance->db->fetch_field(DB_PREFIX . "subscription_group", "subscriptiongroupid=" . $row['subscriptiongroupid'], "title_" . $slng);
			$row['title'] = stripslashes($row['title']);
			$row['description'] = stripslashes($row['description']);
			if ($row['cost'] > 0)
			{
				$row['cost'] = $ilance->currency->format($row['cost']);
			}
			else
			{
				$row['cost'] = '{_free}';
			}
			$row['units'] = print_unit($row['units']);
			if ($row['active'] > 0)
			{
				$row['move'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_migrate-subscription-users&amp;id=' . $row['subscriptionid'] . '" title="Migrate '.$row['active'].' users to a different subscription plan">'.'{_migrate}'.'</a>';
			}
			else
			{
				$row['move'] = '<span style="color:#888888" title="{_cannot_migrate_users__no_users_exist_within_plan}">'.'{_migrate}'.'</span>';
			}
			$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-subscription-plan&amp;id=' . $row['subscriptionid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
			$sql_migrateto = $ilance->db->query("
				SELECT s.migrateto
				FROM " . DB_PREFIX . "subscription s
				WHERE migrateto = '" . $row['subscriptionid'] . "'
				LIMIT 1
			");
			$is_migrateto_plan = ($ilance->db->num_rows($sql_migrateto) > 0) ? 1 : 0;
			if ($row['canremove'] == 1 AND $row['active'] == 0 AND $is_migrateto_plan == 0)
			{
				$row['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_remove-subscription-plan&amp;id=' . $row['subscriptionid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			}
			else
			{
				$row['remove'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" border="0" alt="{_users_exist_please_migrate_all_users_before_removing_an_existing_subscription_plan}" />';
			}
			if ($row['visible_registration'] == '1')
			{
				$row['isvisible_registration'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="{_yes}" />';
			}
			else 
			{
				$row['isvisible_registration'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="{_no}" />';
			}
			if ($row['visible_upgrade'] == '1')
			{
				$row['isvisible_upgrade'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="{_no}" />';
			}    
			else 
			{
				$row['isvisible_upgrade'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="{_no}" />';
			}
			$row['action'] = '<input type="radio" name="subscriptionid" id="subscriptionid" value="' . $row['subscriptionid'] . '" />';
			$row['access'] = $row['subscriptiongroupname'];
			$sqlr = $ilance->db->query("
				SELECT title_$slng as title
				FROM " . DB_PREFIX . "subscription_roles
				WHERE roleid = '" . $row['roleid'] . "'
			");
			if ($ilance->db->num_rows($sqlr) > 0)
			{
				$resrole = $ilance->db->fetch_array($sqlr);
				$row['usingrole'] = stripslashes($resrole['title']);
			}
			else
			{
				$row['usingrole'] = '<span style="color:red">{_no_role_assigned}</span>';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$subscription_rows[] = $row;
			$row_count++;
		}
		$show['no_subscription_rows'] = false;
	}
	else
	{
		$show['no_subscription_rows'] = true;
	}
	    
	$resgroups = $ilance->db->query("
		SELECT subscriptiongroupid, canremove, title_$slng as title, description_$slng as description 
		FROM " . DB_PREFIX . "subscription_group 
		ORDER BY title ASC
	");
	$num_groups = $ilance->db->num_rows($resgroups);
	if ($num_groups > 0)
	{
		while ($row = $ilance->db->fetch_array($resgroups, DB_ASSOC))
		{
			$sqlplans = $ilance->db->query("
				SELECT title_$slng as title
				FROM " . DB_PREFIX . "subscription
				WHERE subscriptiongroupid = '" . $row['subscriptiongroupid'] . "'
			");
			if ($ilance->db->num_rows($sqlplans) > 0)
			{
				while ($resplans = $ilance->db->fetch_array($sqlplans, DB_ASSOC))
				{
					if (isset($row['plans_in_group']))
					{
						$row['plans_in_group'] .= $resplans['title'] . ", ";
					}
					else
					{
						$row['plans_in_group'] = $resplans['title'] . ", ";
					}
				}
				$row['plans_in_group'] = mb_substr($row['plans_in_group'], 0, -2);
				$noplans = 0;
			}
			else
			{
				$noplans = 1;
				$row['plans_in_group'] = '-';
			}
			$row['subscriptiongroupid'] = $row['subscriptiongroupid'];
			$row['subscriptiongroupname'] = $ilance->db->fetch_field(DB_PREFIX . "subscription_group", "subscriptiongroupid=" . $row['subscriptiongroupid'], "title_" . $slng);
			$row['gtitle'] = stripslashes($row['title']);
			$row['gdescription'] = stripslashes($row['description']);
			$sqlsetup = $ilance->db->query("
				SELECT * FROM " . DB_PREFIX . "subscription_permissions
				WHERE subscriptiongroupid = '" . $row['subscriptiongroupid'] . "'
			");
			if ($ilance->db->num_rows($sqlsetup) > 0)
			{
				$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-subscription-group&amp;id='.$row['subscriptiongroupid'].'"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
			}
			else
			{
				$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-subscription-group&amp;id='.$row['subscriptiongroupid'].'" onclick="return confirm_js(\'{_remember_you_must_click_permissions_on_the_next_page_and_then_scroll}\');"><strong>{_set_up}</strong></a>';
			}
			if ($noplans AND $num_groups > 1)
			{
				$row['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_remove-subscription-group&amp;id=' . $row['subscriptiongroupid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action_continue}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a>';					    
				$row['move'] = '-';
			}
			else
			{
				$row['remove'] = '-';
				$row['move'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_migrate-subscription-group&amp;id='.$row['subscriptiongroupid'].'">{_migrate}</a>';
			}
			$row['class'] = ($row_count % 2) ? 'alt1' : 'alt2';
			$subscription_group_rows[] = $row;
			$row_count++;
		}
		$show['no_subscription_group_rows'] = false;
	}
	else
	{
		$show['no_subscription_group_rows'] = true;
	}
	    
	if (isset($new_resource_item))
	{
		$new_resource_item .= "<tr>";
	}
	else
	{
		$new_resource_item = "<tr>";
	}
	$new_resource_item .= "<td class=\"" . $row['class'] . "\"><input type=\"text\" name=\"accesstext\" class=\"textfield\"></td>";
	$new_resource_item .= "<td class=\"" . $row['class'] . "\"><div align=\"center\" class=\"smaller\"><select name='accesstype'><option value='yesno'>yesno</option><option value='int'>int</option></select></div></td>";
	$new_resource_item .= "<td class=\"" . $row['class'] . "\"><div align=\"center\" class=\"smaller\"><input type=\"text\" name=\"accessname\" class=\"input\" size=\"3\" /></div></td>";
	$new_resource_item .= "<td class=\"alt1\" align=\"center\"><input type=\"text\" name=\"value\" class=\"input\" size=\"3\" \></td>";
	$new_resource_item .= "<td class=\"alt1\" align=\"center\"><input type='submit' name='newaccess' value='" . '{_save}' . "'></td>";
	$new_resource_item .= "</tr>";
	$currency = print_left_currency_symbol();
	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';   
	$sql_permgroups = $ilance->db->query("SELECT subscriptiongroupid, canremove, title_$slng as title, description_$slng as description FROM " . DB_PREFIX . "subscription_group");
	$permission_group_pulldown = '<select name="subscriptiongroupid" class="select-250">';
	$permission_group_pulldown .= '<optgroup label="{_subscription_permission_resource}">';
	while ($res_phrasegroups = $ilance->db->fetch_array($sql_permgroups))
	{
		$permission_group_pulldown .= '<option value="' . $res_phrasegroups['subscriptiongroupid'] . '"';
		$permission_group_pulldown .= '>' . stripslashes($res_phrasegroups['title']) . '</option>';
	}
	$permission_group_pulldown .= '</optgroup></select>';                
	$migrate_plan_pulldown = $ilance->admincp->print_migrate_to_pulldown('');
	$migrate_billing_pulldown = $ilance->admincp->print_migrate_billing_pulldown('');
	
	// subscription roles logic
	$sqlroles = $ilance->db->query("
		SELECT roleid, title_$slng as title, purpose_$slng as purpose, custom, roletype, roleusertype, active 
		FROM " . DB_PREFIX . "subscription_roles 
		ORDER BY title_$slng ASC
	");
	if ($ilance->db->num_rows($sqlroles) > 0)
	{
		$row_count = 0;
		while ($resroles = $ilance->db->fetch_array($sqlroles, DB_ASSOC))
		{
			$sqlplans = $ilance->db->query("
				SELECT title_$slng as title
				FROM " . DB_PREFIX . "subscription
				WHERE roleid = '" . $resroles['roleid'] . "'
			");
			if ($ilance->db->num_rows($sqlplans) > 0)
			{
				$count = 1;
				while ($resplans = $ilance->db->fetch_array($sqlplans, DB_ASSOC))
				{
					if (isset($resroles['plans_in_role']))
					{
						$resroles['plans_in_role'] .= '<div style="padding-bottom:3px">' . $count . '. ' . stripslashes($resplans['title']) . "</div>";
					}
					else
					{
						$resroles['plans_in_role'] = '<div style="padding-bottom:3px">' . $count . '. ' . stripslashes($resplans['title']) . "</div>";
					}
					$count++;
				}
			}
			else
			{
				$noplans = 1;
				$resroles['plans_in_role'] = '<span class="gray"><em>{_please_assign_a_plan}</em></span>';
			}
			$resroles['rtitle'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-role&amp;id=' . $resroles['roleid'] . '">' . stripslashes($resroles['title']) . '</a>';
			$resroles['rpurpose'] = '<div class="smaller" style="padding-top:3px">' . stripslashes($resroles['purpose']) . '</div>';
			$resroles['active'] = ($resroles['active']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$resroles['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-role&amp;id=' . $resroles['roleid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$sql_sub = $ilance->db->query("
				SELECT subscriptionid 	
				FROM " . DB_PREFIX . "subscription
				WHERE roleid = '" . $resroles['roleid'] . "'
				LIMIT 1
			");
			$can_remove = ($ilance->db->num_rows($sql_sub) > 0) ? 0 : 1;
			if ($can_remove)
			{
				$resroles['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_remove-role&amp;id=' . $resroles['roleid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action_continue}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			}
			else 
			{
				$resroles['remove'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" border="0" />';
			}
			$resroles['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$subscription_roles[] = $resroles;
			$row_count++;
		}		
	}
	else
	{
		$show['no_subscription_roles'] = true;
	}
	$languages_role = $languages_plan = $languages_permission_group = array();
	$validate_title = $validate_description = '';
	$sql_lang = $ilance->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
	while ($res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
	{
		$languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
		$validate_title .= '(window.document.addsubscriptionplan.title_' . $languagecode . '.value == \'\') ? showImage("titleerror_' . $languagecode . '", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("titleerror_' . $languagecode . '", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);';
		$validate_description .= '(window.document.addsubscriptionplan.description_' . $languagecode . '.value == \'\') ? showImage("descriptionerror_' . $languagecode . '", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("descriptionerror_' . $languagecode . '", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);';
		$language_r['language'] = $language_p['language'] = $language_pg['language'] = $res_lang['title'];
		$language_r['languagecode'] = $language_p['languagecode'] = $language_pg['languagecode'] = $languagecode;
		$languages_role[] = $language_r;
		$languages_plan[] = $language_p;
		$languages_permission_group[] = $language_pg;
	}
	$headinclude .= '
<script language="javascript">
<!--
function validate_title(f)
{
    haveerrors = 0;
    ' . $validate_title . '
    return (!haveerrors);
}
function validate_description(f)
{
    haveerrors = 0;
    ' . $validate_description . '
    return (!haveerrors);
}
function validate_all()
{	
    var title = validate_title();
    var description = validate_description();
    if(!title || !description)
    {
	alert_js(phrase[\'_please_fill_all_fields\']);
	return false;
    }
}
//-->
</script>';
	$roleselected = isset($roleid) ? intval($roleid) : '';
	$role_pulldown = $ilance->subscription_role->print_role_pulldown($roleselected, 1, 1, 1);
	$roleusertypepulldown = $ilance->admincp->print_roleusertype_pulldown();
	$roletypepulldown = $ilance->admincp->print_roletype_pulldown();
	for ($i = 1;$i < 31;$i++){$arr[$i] = $i;}
	$durationpulldown = construct_pulldown('duration', 'duration', $arr, '', 'class="select-75"');
	$subscriptions_settings = $ilance->admincp->construct_admin_input('subscriptions_settings', $ilpage['settings'] . '?cmd=subscriptions');
	
	$pprint_array = array('durationpulldown','subscriptions_settings','roletypepulldown','roleusertypepulldown','role_pulldown','migrate_billing_pulldown','migrate_plan_pulldown','commission_group_pulldown','permission_group_pulldown','currency','new_resource_item','sort');
	
	($apihook = $ilance->api('admincp_subscriptions_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'subscriptions.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'subscription_rows');
	$ilance->template->parse_loop('main', 'subscription_group_rows');
	$ilance->template->parse_loop('main', 'commission_rows');
	$ilance->template->parse_loop('main', 'subscription_roles');
	$ilance->template->parse_loop('main', 'subscription_report_rows');
	$ilance->template->parse_loop('main', 'languages_role');
	$ilance->template->parse_loop('main', 'languages_plan');
	$ilance->template->parse_loop('main', 'languages_permission_group');
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