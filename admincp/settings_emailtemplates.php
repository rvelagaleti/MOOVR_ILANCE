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

$area_title = 'AdminCP - {_email_templates_management_menu}';
$page_title = SITE_NAME . ' - {_email_templates_management_menu}';

($apihook = $ilance->api('admincp_emailtemplates_settings')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=emailtemplates', $_SESSION['ilancedata']['user']['slng']);
// #### DOWNLOAD XML EMAIL PACKAGE #####################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_download-xml-emails')
{
	$area_title = '{_exporting_email_language_pack_via_xml}';
	$page_title = SITE_NAME . ' - {_exporting_email_language_pack_via_xml}';
	$languageid = (isset($ilance->GPC['languageid'])) ? intval($ilance->GPC['languageid']) : 0;
	$ilance->admincp_importexport = construct_object('api.admincp_importexport');
	$ilance->admincp_importexport->export('email', 'admincp', $languageid, '', false, 0);
	exit();
}
// #### UPLOAD XML EMAIL PACKAGE #######################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_upload-xml-emails')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_importing_email_pack_via_xml}';
	$page_title = SITE_NAME . ' - {_importing_email_pack_via_xml}';
	while (list($key, $value) = each($_FILES))
		$GLOBALS["$key"] = $value;
	foreach ($_FILES AS $key => $value)
	{
		$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
		foreach ($value AS $ext => $value2)
		{
			$key2 = $key . '_' . $ext;
			$GLOBALS["$key2"] = $value2;
		}
	}
	$xml = file_get_contents($xml_file);
	$noversioncheck = isset($ilance->GPC['noversioncheck']) ? intval($ilance->GPC['noversioncheck']) : 0;
	$overwritephrases = isset($ilance->GPC['overwrite']) ? intval($ilance->GPC['overwrite']) : 0;
	$ilance->admincp_importexport = construct_object('api.admincp_importexport');
	$ilance->admincp_importexport->import('email', 'admincp', $xml, false, $noversioncheck, $overwritephrases);
	exit();
}
// #### UPDATE EMAIL TEMPLATE PREVIEW ##########################
else if (!empty($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-email-template' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] != '')
{
	$id = $ilance->GPC['id'];
	$area_title = '{_updating_email_template} #' . $id;
	$page_title = SITE_NAME . ' - {_updating_email_template} #' . $id;
	$sqllang = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "language
	");
	while ($langres = $ilance->db->fetch_array($sqllang, DB_ASSOC))
	{
		$langres['langshort'] = mb_strtolower(mb_substr($langres['languagecode'], 0, 3));
		$langres['language'] = $langres['title'];
		$sql = $ilance->db->query("
			SELECT id, name_" . $langres['langshort'] . " AS name, varname, message_" . $langres['langshort'] . " AS message, subject_" . $langres['langshort'] . " AS subject, product, cansend, departmentid, buyer, seller, admin, type, ishtml
			FROM " . DB_PREFIX . "email
			WHERE id = '" . intval($id) . "' OR varname = '" . $ilance->db->escape_string($id) . "'
		");
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$id = $res['id'];
		$langres['name'] = stripslashes($res['name']);
		$langres['name'] = handle_input_keywords($langres['name']);
		$langres['subject'] = stripslashes($res['subject']);
		//$langres['message'] = print_wysiwyg_editor('message_' . $langres['langshort'], nl2br($res['message']), 'bbeditor', '1', '1', true, '850', '400', '', 'ckeditor', '');
		if ($res['ishtml'])
		{
			$langres['message'] = print_wysiwyg_editor('message_' . $langres['langshort'], $res['message'], 'bbeditor', '1', '1', true, '850', '400', '', 'ckeditor', '');	
		}
		else
		{
			$langres['message'] = '<textarea name="message_' . $langres['langshort'] . '" id="message_' . $langres['langshort'] . '" style="font: 10pt consolas, \'courier new\', courier, monospace; width: 98%; height: 425px; background-color: #fff; color:#222" class="input" wrap="physical">' . $res['message'] . '</textarea>';
		}
		$langres['varname'] = stripslashes($res['varname']);
		$langres['typeglobal'] = $langres['typeservice'] = $langres['typeproduct'] = $langres['usertypegeneral'] = $langres['usertypebuyer'] = $langres['usertypeseller'] = $langres['usertypeadmin'] = $langres['ishtml'] = '';
		if ($res['type'] == 'global')
		{
			$langres['typeglobal'] = 'checked="checked"';
			$langres['typeservice'] = '';
			$langres['typeproduct'] = '';
		}
		else if ($res['type'] == 'service')
		{
			$langres['typeglobal'] = '';
			$langres['typeservice'] = 'checked="checked"';
			$langres['typeproduct'] = '';
		}
		else if ($res['type'] == 'product')
		{
			$langres['typeglobal'] = '';
			$langres['typeservice'] = '';
			$langres['typeproduct'] = 'checked="checked"';
		}

		if ($res['buyer'] == '0' AND $res['seller'] == '0' AND $res['admin'] == '0')
		{
			$langres['usertypegeneral'] = 'checked="checked"';
			$langres['usertypebuyer'] = 'disabled="disabled"';
			$langres['usertypeseller'] = 'disabled="disabled"';
			$langres['usertypeadmin'] = 'disabled="disabled"';
		}
		else
		{
			if ($res['buyer'])
			{
				$langres['usertypebuyer'] = 'checked="checked"';
			}
			if ($res['seller'])
			{
				$langres['usertypeseller'] = 'checked="checked"';
			}
			if ($res['admin'])
			{
				$langres['usertypeadmin'] = 'checked="checked"';
			}
		}
		if (preg_match_all("!\{\{[a-z0-9_]+\}\}!", $langres['message'], $matches))
		{
			foreach ($matches[0] AS $key => $value)
			{
				$matchesx[0][$key] = str_replace("{{", "&#123;&#123;", $value);
				$matchesx[0][$key] = str_replace("}}", "&#125;&#125;", $value);
			}
			$langres['emailobjects'] = implode(LINEBREAK, array_unique($matchesx[0]));
		}
		if ($res['ishtml'])
		{
			$langres['ishtml'] = 'checked="checked"';	
		}
		else
		{
			$langres['ishtml'] = '';
		}
		$langres['page'] = isset($ilance->GPC['page']) ? intval($ilance->GPC['page']) : 1;
		$langres['nextid'] = ($res['id'] + 1);
		$langres['previd'] = ($res['id'] - 1);
		$products_pulldown = $ilance->admincp->products_pulldown($res['product']);
		$department_pulldown = $ilance->admincp->email_departments_pulldown($res['departmentid']);
		$email_languages[] = $langres;
	}
	$show['update_template'] = true;
	$show['list_template'] = false;

	$pprint_array = array ('hiddeninput', 'department_pulldown', 'action', 'submitname', 'emailsubcmd', 'title', 'email', 'varname', 'keywords', 'products_pulldown', 'langshort', 'language_pulldown', 'page', 'name', 'subject', 'body', 'emailobjects', 'id', 'prevnext');

	($apihook = $ilance->api('admincp_emailtemplates_update_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'emailtemplates_update.html', 1);
	$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'email_languages');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### UPDATE EMAIL TEMPLATE HANDLER ##########################
else if (!empty($ilance->GPC['do']) AND $ilance->GPC['do'] == '_update-email-template' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['subject']) AND !empty($ilance->GPC['langshort']) AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_updating_email_template} - ' . handle_input_keywords($ilance->GPC['name']);
	$page_title = SITE_NAME . ' - {_updating_email_template} - ' . handle_input_keywords($ilance->GPC['name']);
	$subject = $ilance->GPC['subject'];
	$index = 'message_' . $ilance->GPC['langshort'];
	//$message = remove_newline($ilance->GPC[$index]);
	$message = $ilance->GPC["$index"];
	$name = handle_input_keywords($ilance->GPC['name']);
	$buyer = (isset($ilance->GPC['usertype']) AND $ilance->GPC['usertype'] == 'buyer') ? 1 : 0;
	$seller = (isset($ilance->GPC['usertype']) AND $ilance->GPC['usertype'] == 'seller') ? 1 : 0;
	$admin = (isset($ilance->GPC['usertype']) AND $ilance->GPC['usertype'] == 'admin') ? 1 : 0;
	$type = isset($ilance->GPC['type']) ? $ilance->GPC['type'] : 'global';
	$ishtml = isset($ilance->GPC['ishtml']) ? 1 : 0;
	if (isset($ilance->GPC['usertype']) AND $ilance->GPC['usertype'] == 'general')
	{
		$buyer = $seller = $admin = 0;
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "email
		SET name_" . $ilance->db->escape_string($ilance->GPC['langshort']) . " = '" . $ilance->db->escape_string($name) . "',
		message_" . $ilance->db->escape_string($ilance->GPC['langshort']) . " = '" . $ilance->db->escape_string($message) . "',
		subject_" . $ilance->db->escape_string($ilance->GPC['langshort']) . " = '" . $ilance->db->escape_string($subject) . "',
		varname = '" . $ilance->db->escape_string($ilance->GPC['varname']) . "',
		product = '" . $ilance->db->escape_string($ilance->GPC['product']) . "',
		departmentid = '" . intval($ilance->GPC['departmentid']) . "',
		type = '" . $ilance->db->escape_string($type) . "',
		buyer = '" . intval($buyer) . "',
		seller = '" . intval($seller) . "',
		admin = '" . intval($admin) . "',
		ishtml = '" . intval($ishtml) . "'
		WHERE id = '" . intval($ilance->GPC['id']) . "'
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### INSERT NEW EMAIL TEMPLATE ##############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_insert-email-template' AND isset($ilance->GPC['subject']) AND isset($ilance->GPC['message']) AND isset($ilance->GPC['name']) AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if (empty($ilance->GPC['subject']) OR empty($ilance->GPC['message']) OR empty($ilance->GPC['name']))
	{
		print_action_failed('{_please_fill_all_fields}', $ilpage['settings'] . '?cmd=emailtemplates');
		exit();
	}
	$area_title = '{_adding_new_email_template}';
	$page_title = SITE_NAME . ' - {_adding_new_email_template}';
	$ids = $val = '';
	$buyer = $seller = $admin = 0;
	$subject = $ilance->GPC['subject'];
	$message = $ilance->GPC['message'];
	$name = ilance_htmlentities($ilance->GPC['name']);
	$buyer = (isset($ilance->GPC['usertype']) AND $ilance->GPC['usertype'] == 'buyer') ? 1 : 0;
	$seller = (isset($ilance->GPC['usertype']) AND $ilance->GPC['usertype'] == 'seller') ? 1 : 0;
	$admin = (isset($ilance->GPC['usertype']) AND $ilance->GPC['usertype'] == 'admin') ? 1 : 0;
	$ishtml = isset($ilance->GPC['ishtml']) ? 1 : 0;
	if (isset($ilance->GPC['usertype']) AND $ilance->GPC['usertype'] == 'general')
	{
		$buyer = $seller = $admin = 0;
	}
	$sql = $ilance->db->query("
		SELECT languagecode
		FROM " . DB_PREFIX . "language
	");
	while ($langres = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$ids .= " message_" . mb_substr($langres['languagecode'], 0, 3) . ", subject_" . mb_substr($langres['languagecode'], 0, 3) . ", name_" . mb_substr($langres['languagecode'], 0, 3) . ", ";
		$val .= "'" . $ilance->db->escape_string($message) . "', '" . $ilance->db->escape_string($subject) . "', '" . $ilance->db->escape_string($name) . "', ";
	}
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "email
		(id, varname, subject_original, message_original, " . $ids . " type, product, cansend, departmentid, buyer, seller, admin, ishtml)
		VALUES(
		NULL,
		'" . $ilance->db->escape_string($ilance->GPC['varname']) . "',
		'" . $ilance->db->escape_string($subject) . "',
		'" . $ilance->db->escape_string($message) . "',
		$val
		'" . $ilance->db->escape_string($ilance->GPC['type']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['product']) . "',
		'1',
		'" . intval($ilance->GPC['departmentid']) . "',
		'" . intval($buyer) . "',
		'" . intval($seller) . "',
		'" . intval($admin) . "',
		'" . intval($ishtml) . "')
	");
	$id = $ilance->db->insert_id();
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=_update-email-template&amp;id=' . $id);
	exit();
}
// #### REMOVE EMAIL DEPARTMENT HANDLER ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removedepartment' AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_removing_email_department} #' . intval($ilance->GPC['departmentid']);
	$page_title = SITE_NAME . ' - {_removing_email_department} #' . intval($ilance->GPC['departmentid']);
	$sql = $ilance->db->query("
		SELECT canremove
		FROM " . DB_PREFIX . "email_departments
		WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
	");
	$res = $ilance->db->fetch_array($sql);
	if ($res['canremove'] == '1')
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "email_departments
			WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
		");
		// select the default non-removable department
		$sql2 = $ilance->db->query("
			SELECT departmentid
			FROM " . DB_PREFIX . "email_departments
			WHERE canremove = '0'
		");
		$res2 = $ilance->db->fetch_array($sql2);
		// migrate all email templates in this department to the default non-removable department
		$ilance->db->query("
				UPDATE " . DB_PREFIX . "email
			SET departmentid = '" . $res2['departmentid'] . "'
			WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
		");
		print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=emailtemplates');
		exit();
	}
	else
	{
		print_action_failed('{_email_department_could_not_be_removed_this_is_your_default_email}', $ilpage['settings'] . '?cmd=emailtemplates');
		exit();
	}
}
// #### ADD EMAIL DEPARTMENT HANDLER ###########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'adddepartment' AND isset($ilance->GPC['title']) AND $ilance->GPC['title'] != '' AND isset($ilance->GPC['email']) AND $ilance->GPC['email'] != '')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_adding_new_email_department}';
	$page_title = SITE_NAME . ' - {_adding_new_email_department}';
	$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "email_departments
			(departmentid, title, email, canremove)
			VALUES (
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['email']) . "',
			'1')
		");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE EMAIL DEPARTMENT HANDLER ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'doupdatedepartment' AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0 AND isset($ilance->GPC['title']) AND $ilance->GPC['title'] != '' AND isset($ilance->GPC['email']) AND $ilance->GPC['email'] != '')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_updating_email_department}';
	$page_title = SITE_NAME . ' - {_updating_email_department}';
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "email_departments
		SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
		email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
		WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
else
{
	$show['update_template'] = false;
	$show['list_template'] = true;
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	$rowlimit = '10';
	$counter = ($ilance->GPC['page'] - 1) * $rowlimit;
	$orderlimit = ' ORDER BY id ASC LIMIT ' . (($ilance->GPC['page'] - 1) * $rowlimit) . ',' . $rowlimit;
	// are we searching for a particular email template?
	$extrasql = '';
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
	{
		$extrasql = "WHERE id > 0 ";
		if (isset($ilance->GPC['id']) AND !empty($ilance->GPC['id']))
		{
			$ilance->GPC['id'] = trim($ilance->GPC['id']);
			$extrasql .= "AND (id = '" . $ilance->GPC['id'] . "' OR varname = '" . $ilance->db->escape_string($ilance->crypt->three_layer_decrypt($ilance->GPC['id'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) . "') ";
		}
		if (isset($ilance->GPC['varname']) AND !empty($ilance->GPC['varname']))
		{
			$extrasql .= "AND varname = '" . $ilance->db->escape_string($ilance->GPC['varname']) . "'";
		}
		if (isset($ilance->GPC['keywords']) AND !empty($ilance->GPC['keywords']))
		{
		    $langsql = '';
		    $languages = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language");
		    while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		    {
			    $slng = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			    $keywords = $ilance->db->escape_string(trim($ilance->GPC['keywords']));
			    $langsql .= !empty($langsql) ? " OR " : "";
			    $langsql .= "subject_" . $slng . " LIKE '%" . $keywords . "%' OR message_" . $slng . " LIKE '%" . $keywords . "%' OR name_" . $slng . " LIKE '%" . $keywords . "%'";
		    }
		    $extrasql .= " AND (" . $langsql . ") ";
		}
		if (isset($ilance->GPC['product']) AND !empty($ilance->GPC['product']))
		{
			$extrasql .= "AND product = '" . $ilance->db->escape_string($ilance->GPC['product']) . "'";
		}
		if (isset($ilance->GPC['type']) AND !empty($ilance->GPC['type']))
		{
			if ($ilance->GPC['type'] == 'general')
			{
				$extrasql .= "AND buyer = '0' AND seller = '0' AND admin = '0'";
			}
			else if ($ilance->GPC['type'] == 'buyer')
			{
				$extrasql .= "AND buyer = '1'";
			}
			else if ($ilance->GPC['type'] == 'seller')
			{
				$extrasql .= "AND seller = '1'";
			}
			else if ($ilance->GPC['type'] == 'admin')
			{
				$extrasql .= "AND admin = '1'";
			}
		}
		if (isset($ilance->GPC['templatetype']) AND !empty($ilance->GPC['templatetype']) AND in_array($ilance->GPC['templatetype'], array('product', 'service', 'global')))
		{
		    $extrasql .= " AND type = '" . $ilance->db->escape_string($ilance->GPC['templatetype']) . "'";
		}
	}
	$sql = $ilance->db->query("
		SELECT id, varname, name_" . $_SESSION['ilancedata']['user']['slng'] . " AS name, message_" . $_SESSION['ilancedata']['user']['slng'] . " AS body, subject_" . $_SESSION['ilancedata']['user']['slng'] . " AS subject, product, cansend, departmentid, buyer, seller, admin, type
		FROM " . DB_PREFIX . "email
		$extrasql
		$orderlimit
	");
	$sql2 = $ilance->db->query("
		SELECT id
		FROM " . DB_PREFIX . "email
		$extrasql
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$number = $ilance->db->num_rows($sql2);
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			if ($res['product'] == 'ilance')
			{
				$res['product'] = 'ILance';
			}
			else
			{
				$res['product'] = ucfirst($res['product']);
			}
			$res['action'] = '<div><input type="button" name="edit_template_' . $res['varname'] . '" id="edit_template_' . $res['varname'] . '" value="{_edit}" class="buttons" style="font-size:10px" onclick="location.href=\'' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=_update-email-template&amp;id=' . $res['id'] . '&amp;page=' . intval($ilance->GPC['page']) . '\'" /></div><div style="padding-top:6px"><input type="button" name="dispatch_test_' . $res['varname'] . '" id="dispatch_test_' . $res['varname'] . '" value="{_email_test}" class="buttons" style="font-size:10px" onclick="return send_test_email_template(\'' . $res['varname'] . '\');" /></div>';
			$res['department'] = $ilance->admincp->fetch_email_department_title($res['departmentid']);
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$res['name'] = '<a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=_update-email-template&amp;id=' . $res['id'] . '&amp;page=' . intval($ilance->GPC['page']) . '">' . handle_input_keywords($res['name']) . '</a>';
			$res['ebuyer'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$res['eseller'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$res['eadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$res['egeneral'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			if ($res['buyer'])
			{
				$res['ebuyer'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
			}
			if ($res['seller'])
			{
				$res['eseller'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
			}
			if ($res['admin'])
			{
				$res['eadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
			}
			if ($res['buyer'] == '0' AND $res['seller'] == '0' AND $res['admin'] == '0')
			{
				$res['egeneral'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
			}
			$res['type'] = ucfirst($res['type']);
			$email_templates[] = $res;
			$row_count++;
		}
		// settings.php?cmd=emailtemplates&subcmd=search&id=&varname=&keywords=This+email+is+to+inform+you+that&product=ilance
		$subcmd = (isset($ilance->GPC['subcmd']) AND !empty($ilance->GPC['subcmd'])) ? $ilance->GPC['subcmd'] : '';
		$id = (isset($ilance->GPC['id']) AND !empty($ilance->GPC['id'])) ? $ilance->GPC['id'] : '';
		$varname = (isset($ilance->GPC['varname']) AND !empty($ilance->GPC['varname'])) ? $ilance->GPC['varname'] : '';
		$keywords = (isset($ilance->GPC['keywords']) AND !empty($ilance->GPC['keywords'])) ? $ilance->GPC['keywords'] : '';
		$product = (isset($ilance->GPC['product']) AND !empty($ilance->GPC['product'])) ? $ilance->GPC['product'] : 'ilance';
		$type = (isset($ilance->GPC['type']) AND !empty($ilance->GPC['type'])) ? $ilance->GPC['type'] : '';
		$templatetype = (isset($ilance->GPC['templatetype']) AND !empty($ilance->GPC['templatetype'])) ? $ilance->GPC['templatetype'] : '';
		$extra = '&amp;subcmd=' . $subcmd . '&amp;id=' . $id . '&amp;varname=' . $varname . '&amp;keywords=' . $keywords . '&amp;product=' . $product . '&amp;type=' . $type . '&templatetype=' . $templatetype;
		$prevnext = print_pagnation($number, $rowlimit, $ilance->GPC['page'], $counter, $ilpage['settings'] . '?cmd=emailtemplates' . $extra);
	}
	$product = isset($ilance->GPC['product']) ? $ilance->GPC['product'] : '';
	$products_pulldown = $ilance->admincp->products_pulldown($product);
	$department_pulldown = $ilance->admincp->email_departments_pulldown();
	// #### EMAIL DEPARTMENT MANAGEMENT ####################
	$title = $email = $hiddeninput = '';
	$emailsubcmd = 'adddepartment';
	$submitname = '{_add} {_department}';
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatedepartment' AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0)
	{
		$emailsubcmd = 'doupdatedepartment';
		$submitname = '{_update}';
		$hiddeninput = '<input type="hidden" name="departmentid" value="' . intval($ilance->GPC['departmentid']) . '" />';
		$sql = $ilance->db->query("
			SELECT departmentid, title, email
			FROM " . DB_PREFIX . "email_departments
			WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$title = $res['title'];
			$email = $res['email'];
		}
	}
	$sql = $ilance->db->query("
		SELECT departmentid, title, email, canremove
		FROM " . DB_PREFIX . "email_departments
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sql))
		{
			$res['templatecount'] = $ilance->admincp->fetch_email_department_count($res['departmentid']);
			if ($res['canremove'])
			{
				$res['action'] = '<a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=updatedepartment&amp;departmentid=' . $res['departmentid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a> &nbsp; <a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=removedepartment&amp;departmentid=' . $res['departmentid'] . '" style="color:#990000" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			}
			else
			{
				$res['action'] = '<a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=updatedepartment&amp;departmentid=' . $res['departmentid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$res['class'] = 'featured_highlight';
			}
			$email_departments[] = $res;
			$row_count++;
		}
	}
}
$language_pulldown = $ilance->language->print_language_pulldown();
$keywords = isset($ilance->GPC['keywords']) ? $ilance->GPC['keywords'] : '';
$varname = isset($ilance->GPC['varname']) ? $ilance->GPC['varname'] : '';
$event_pulldown = construct_pulldown('event', 'event', array('latest' => '{_latest}', 'ending_soon' => '{_ending_soon}', 'featured' => '{_featured}'), 'latest', '');
$auctiontype_pulldown = construct_pulldown('auctiontype', 'auctiontype', array('product' => '{_product}', 'service' => '{_service}'), 'product', '');
$selected_templatetype = isset($ilance->GPC['templatetype']) ? $ilance->db->escape_string($ilance->GPC['templatetype']) : $ilconfig['globalauctionsettings_auctionstypeenabled'];
$templatetype_pulldown = construct_pulldown('templatetype', 'templatetype', array('' => '{_all}', 'global' => '{_global}', 'product' => '{_product}', 'service' => '{_service}'), $selected_templatetype, 'class="select"');
$emails_settings = $ilance->admincp->construct_admin_input('emailssettings', $ilpage['settings'] . '?cmd=emailtemplates');

$pprint_array = array ('emails_settings','templatetype_pulldown','auctiontype_pulldown','event_pulldown','hiddeninput', 'department_pulldown', 'action', 'submitname', 'emailsubcmd', 'title', 'email', 'varname', 'keywords', 'products_pulldown', 'langshort', 'language_pulldown', 'page', 'name', 'subject', 'body', 'emailobjects', 'id', 'prevnext');

($apihook = $ilance->api('admincp_emailtemplates_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'emailtemplates.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', array('email_templates', 'email_departments'));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();
?>