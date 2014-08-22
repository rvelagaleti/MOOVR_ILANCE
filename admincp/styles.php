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
// #### load required javascript ###############################################
$jsinclude = array(
	'header' => array(
		'functions',
		'ajax',
		'tabfx'
	),
	'footer' => array(
		'tooltip',
		'cron'
	)
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
define('AREA', 'styles');
// #### require backend ########################################################
require_once('./../functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array ("$ilpage[styles]" => $ilcrumbs["$ilpage[styles]"]);
if (($v3nav = $ilance->cache->fetch("print_admincp_nav_styles")) === false)
{
    $v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['styles']);
    $ilance->cache->store("print_admincp_nav_styles", $v3nav);
}
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
    if (isset($ilance->GPC['cmd']))
    {
	if ($ilance->GPC['cmd'] == '_edit-style' AND isset($ilance->GPC['id']))
	{
	    $id = intval($ilance->GPC['id']);
	    $area_title = '{_exporting_css_styles_to_xml}';
	    $page_title = SITE_NAME . ' - ' . '{_exporting_css_styles_to_xml}';
	    $subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['styles'], $ilpage['styles'], $_SESSION['ilancedata']['user']['slng']);
	    $sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "styles WHERE styleid = '" . $id . "'");
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    $id = $res['styleid'];
	    $name = $res['name'];
	    $sort = $res['sort'];

	    $style_template_vars = '<tr class="alt2_top">
			    <td><b>{_description}</b></td>
			    <td><b>{_varname}</b></td>
			    <td><b>{_original}</b></td>
			    <td><b>{_value}</b></td>
			</tr>';
	    $sql_vars = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "templates WHERE styleid = '" . $id . "' ORDER BY sort");
	    while ($res_vars = $ilance->db->fetch_array($sql_vars, DB_ASSOC))
	    {
		$style_template_vars .= '
			    <tr>
				<td class="alt1">' . $res_vars['description'] . '</td>
				<td class="alt2">' . $res_vars['name'] . '</td>	    
				<td class="alt1"> ' . $res_vars['original'] . '</td>
				<td class="alt2"><textarea id="' . $res_vars['tid'] . '" name="' . $res_vars['name'] . '" class="input">' . handle_input_keywords($res_vars['content']) . '</textarea></td>
			    </tr>';
	    }
	    $pprint_array = array ('name', 'id', 'sort', 'style_template_vars');

	    ($apihook = $ilance->api('admincp_templates_end')) ? eval($apihook) : false;

	    $ilance->template->fetch('main', 'styles_edit.html', 1);
	    $ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
	    $ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	    $ilance->template->parse_if_blocks('main');
	    $ilance->template->pprint('main', $pprint_array);
	    exit();
	}
	else if ($ilance->GPC['cmd'] == '_remove-style' AND isset($ilance->GPC['id']))
	{
	    $id = intval($ilance->GPC['id']);
	    $sql = $ilance->db->query("SELECT styleid FROM " . DB_PREFIX . "styles");
	    $num = $ilance->db->num_rows($sql);
	    if ($id != $ilconfig['defaultstyle'] AND $num > 1)
	    {
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "styles 
			WHERE styleid = '" . $id . "'
		    ");
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "templates
			WHERE styleid = '" . $id . "'
		    ");
		@unlink(DIR_CSS . 'client_' . $id . '.css');
		@unlink(DIR_CSS . 'admin_' . $id . '.css');
		@unlink(DIR_TMP_CSS . 'css_style_' . $id . '_client.css');
		@unlink(DIR_TMP_CSS . 'css_style_' . $id . '_admin.css');
		$ilance->cachecore->delete("templatevars_" . $id);

		print_action_success('{_the_selected_style_group_and_associated_templates_was_removed_from_the_template_system}', $ilpage['styles']);
		exit();
	    }
	    else
	    {
		print_action_failed('{_were_sorry_there_seems_to_be_only_1_available_style}', $ilpage['styles']);
		exit();
	    }
	}
	else if ($ilance->GPC['cmd'] == '_save-style' AND isset($ilance->GPC['id']))
	{
	    $id = intval($ilance->GPC['id']);
	    $name = isset($ilance->GPC['name']) ? $ilance->db->escape_string($ilance->GPC['name']) : '';
	    $sort = isset($ilance->GPC['sort']) ? $ilance->db->escape_string($ilance->GPC['sort']) : '1';
	    $ilance->db->query("
		    UPDATE " . DB_PREFIX . "styles 
		    SET name = '" . $name . "',
			sort = '" . $sort . "',
			filehash = ''
		    WHERE styleid = '" . $id . "'
		");
	    $sql = $ilance->db->query("SELECT name, content FROM " . DB_PREFIX . "templates GROUP BY name");
	    while($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	    {
		$name = $res['name'];
		if (isset($ilance->GPC[$name]) AND $res['content'] != $ilance->GPC[$name])
		{ 
		    $ilance->db->query("UPDATE " . DB_PREFIX . "templates SET content = '" . $ilance->db->escape_string($ilance->GPC[$name]) . "' WHERE name = '" . $name . "' AND styleid = '" . $id . "'");
		}
	    }
	    $ilance->cachecore->delete("templatevars_" . $id);

	    print_action_success('{_the_selected_style_name_title_was_successfully_updated}', $ilpage['styles']);
	    exit();
	}
	else if ($ilance->GPC['cmd'] == '_new-style')
	{
	    $defaultid = intval($ilance->GPC['styleid']);
	    $name = isset($ilance->GPC['name']) ? $ilance->db->escape_string($ilance->GPC['name']) : '';

	    $sql = $ilance->db->query("SELECT styleid FROM " . DB_PREFIX . "styles ORDER BY styleid DESC LIMIT 1");
	    $last = $ilance->db->fetch_array($sql, DB_ASSOC);
	    $newstyleid = intval($last['styleid']) + 1;

	    $client_css = file_get_contents(DIR_CSS . 'client_' . $defaultid . '.css');
	    $admin_css = file_get_contents(DIR_CSS . 'admin_' . $defaultid . '.css');
	    $newclientcss = file_put_contents(DIR_CSS . 'client_' . $newstyleid . '.css', $client_css);
	    $newadmincss = file_put_contents(DIR_CSS . 'admin_' . $newstyleid . '.css', $admin_css);

	    if ($newadmincss > 0 AND $newclientcss > 0)
	    {
		$ilance->db->query("
			    INSERT INTO " . DB_PREFIX . "styles
			    (styleid, name, visible, sort)
			    VALUES(
			    '" . $newstyleid . "',
			    '" . $name . "',
			    '1',
			    '" . $newstyleid . "')
		    ", 0, null, __FILE__, __LINE__);

		$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "templates WHERE styleid = '" . $defaultid . "'");
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
		    $ilance->db->query("
				INSERT INTO " . DB_PREFIX . "templates
				(tid, name, description, original, content, type, isupdated, updatedate, styleid, product, sort)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($res['name']) . "',
				'" . $ilance->db->escape_string($res['description']) . "',
				'" . $ilance->db->escape_string($res['original']) . "',
				'" . $ilance->db->escape_string($res['content']) . "',
				'" . $ilance->db->escape_string($res['type']) . "',
				'0',
				'" . $ilance->db->escape_string($res['updatedate']) . "',
				'" . $newstyleid . "',
				'" . $ilance->db->escape_string($res['product']) . "',
				'" . intval($res['sort']) . "')
			", 0, null, __FILE__, __LINE__);
		}

		print_action_success('{_the_new_style_was_created_and_is_available_to_the_template_system}', $ilance->GPC['return']);
		exit();
	    }
	    else
	    {

	    }
	}
	else if ($ilance->GPC['cmd'] == '_import-style-xml')
	{
	    $area_title = '{_importing_xml_styles_via_xml}';
	    $page_title = SITE_NAME . ' - ' . '{_importing_xml_styles_via_xml}';

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
	    $setasdefault = isset($ilance->GPC['makedefault']) ? intval($ilance->GPC['makedefault']) : 0;

	    $ilance->admincp_importexport = construct_object('api.admincp_importexport');
	    $done = $ilance->admincp_importexport->import('css', 'admincp', $xml, true, $noversioncheck, $overwritephrases, $setasdefault);
	    if ($done)
	    {
		$sql = $ilance->db->query("SELECT styleid FROM " . DB_PREFIX . "styles ORDER BY styleid DESC LIMIT 1");
		$last = $ilance->db->fetch_array($sql, DB_ASSOC);
		$newstyleid = intval($last['styleid']);

		$client_css = file_get_contents(DIR_CSS . 'client_raw.css');
		$admin_css = file_get_contents(DIR_CSS . 'admin_raw.css');
		$newclientcss = file_put_contents(DIR_CSS . 'client_' . $newstyleid . '.css', $client_css);
		$newadmincss = file_put_contents(DIR_CSS . 'admin_' . $newstyleid . '.css', $admin_css);
		print_action_success('{_css_style_importation_success}', $ilpage['styles']);
		exit();
	    }
	    exit();
	}
	if ($ilance->GPC['cmd'] == '_export-style-xml')
	{
		$area_title = '{_exporting_css_styles_to_xml}';
		$page_title = SITE_NAME . ' - ' . '{_exporting_css_styles_to_xml}';
	
		$styleid = (isset($ilance->GPC['styleid'])) ? intval($ilance->GPC['styleid']) : 1;
		$product = (isset($ilance->GPC['product']) AND !empty($ilance->GPC['product'])) ? $ilance->GPC['product'] : 'ilance';
		
		$ilance->admincp_importexport = construct_object('api.admincp_importexport');
		$ilance->admincp_importexport->export('css', 'admincp', 0, '', false, 0, $styleid, $product);
		exit();
	}
	else if ($ilance->GPC['cmd'] == '_new-template-var')
	{
	    $area_title = '{_creating_template_variable}';
	    $page_title = SITE_NAME . ' - ' . '{_creating_template_variable}';

	    $ilance->GPC['product'] = isset($ilance->GPC['product']) ? $ilance->GPC['product'] : 'ilance';
	    $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '100';

	    if (isset($ilance->GPC['gid']) AND isset($ilance->GPC['type']) AND isset($ilance->GPC['author']) AND isset($ilance->GPC['name']) AND isset($ilance->GPC['content']) AND isset($ilance->GPC['description']))
	    {
		// insert new template variable for all installed styles
		$sql = $ilance->db->query("SELECT styleid FROM " . DB_PREFIX . "styles");
		if ($ilance->db->num_rows($sql) > 0)
		{
		    while ($res = $ilance->db->fetch_array($sql))
		    {
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "templates
				(tid, name, description, original, content, type, status, iscustom, createdate, author, version, styleid, product, sort)
				VALUES (
				NULL,
				'" . $ilance->db->escape_string($ilance->GPC['name']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['content']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['content']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['type']) . "',
				'1',
				'1',
				NOW(),
				'" . $ilance->db->escape_string($ilance->GPC['author']) . "',
				'1.0',
				'" . $res['styleid'] . "',
				'" . $ilance->db->escape_string($ilance->GPC['product']) . "',
				'" . intval($ilance->GPC['sort']) . "')
			", 0, null, __FILE__, __LINE__);
			
			$ilance->cachecore->delete("templatevars_" . $res['styleid']);
		    }

		    print_action_success('{_the_new_template_variable_was_created_and_is_available_to_the_template_system}', $ilpage['styles']);
		    exit();
		}
	    }
	}
	else if ($ilance->GPC['cmd'] == '_remove-custom-var' AND isset($ilance->GPC['name']))
	{
	    $area_title = '{_removing_template_variable}';
	    $page_title = SITE_NAME . ' - ' . '{_removing_template_variable}';
		
	    $ilance->db->query("
		DELETE FROM " . DB_PREFIX . "templates
		WHERE name = '" . $ilance->db->escape_string($ilance->GPC['name']) . "'
	    ");
	    
	    print_action_success('{_the_template_variable_was_removed_from_the_template_system}', $ilpage['styles']);
            exit();
	}
    }
    
    $area_title = '{_templates_administration_menu}';
    $page_title = SITE_NAME . ' - ' . '{_templates_administration_menu}';

    ($apihook = $ilance->api('admincp_template_management')) ? eval($apihook) : false;

    $subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['styles'], $ilpage['styles'], $_SESSION['ilancedata']['user']['slng']);

    $styles_pulldown = (isset($ilance->GPC['styleid'])) ? $ilance->styles->print_styles_pulldown($ilance->GPC['styleid']) : $ilance->styles->print_styles_pulldown();
    $productselected = isset($ilance->GPC['product']) ? $ilance->GPC['product'] : 'ilance';
    $products_pulldown = $ilance->admincp->products_pulldown($productselected);
    $styles_table = '';
    $sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "styles ORDER BY sort");
    $num = $ilance->db->num_rows($sql);
    while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
    {
	$edit = '<a href="' . $ilpage['styles'] . '?cmd=_edit-style&amp;id=' . $res['styleid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
	$remove = ($num > 1 AND $res['styleid'] != $ilconfig['defaultstyle']) ? '<a href="' . $ilpage['styles'] . '?cmd=_remove-style&amp;id=' . $res['styleid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" alt="" border="0">';
	$client_file = (file_exists(DIR_CSS . 'client_' . $res['styleid'] . '.css')) ? '' : '<span class="smaller red">(Does not exist, use client_raw.css)</span>';
	$admin_file = (file_exists(DIR_CSS . 'admin_' . $res['styleid'] . '.css')) ? '' : '<span class="smaller red">(Does not exist, use admin_raw.css)</span>';
	
	
	$default = ($res['styleid'] == $ilconfig['defaultstyle']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0">' : '';

	$styles_table .= '
		<tr class="alt1" valign="top">
		    <td>' . $res['styleid'] . '</td>
		    <td>' . $res['name'] . '</td>
		    <td>/' . DIR_FUNCT_NAME . '/' . DIR_CSS_NAME . '/client_' . $res['styleid'] . '.css ' . $client_file . '<br />/' . DIR_FUNCT_NAME . '/' . DIR_CSS_NAME . '/admin_' . $res['styleid'] . '.css ' . $admin_file . '</td>
		    <td>' . $default . '</td>
		    <td>' . (($res['visible']) ? '{_yes}' : '{_no}') . '</td>
		    <td>' . $res['sort'] . '</td>
		    <td>' . $edit . '&nbsp;&nbsp;' . $remove . '</td>
		</tr>';
    }
    $template_custom_vars = '';
    $sql_tcv = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "templates WHERE iscustom='1' GROUP BY name");
    $num_tcv = $ilance->db->num_rows($sql_tcv);
    if ($num_tcv > 0)
    {
	$template_custom_vars = '
	    <tr class="alt1">
		<td><b>{_template_variable}</b></td>
		<td><b>{_description}</b></td>
		<td><b>{_template_data}</b></td>
		<td><b>{_actions}</b></td>
	    </tr>';
	while ($res_tcv = $ilance->db->fetch_array($sql_tcv, DB_ASSOC))
	{
	    $remove = '<a href="' . $ilpage['styles'] . '?cmd=_remove-custom-var&amp;name=' . $res_tcv['name'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
	    $template_custom_vars .= '
		<tr class="alt1">
		    <td>' . $res_tcv['name'] . '</td>
		    <td>' . $res_tcv['description'] . '</td>
		    <td>' . $res_tcv['original'] . '</td>
		    <td>' . $remove . '</td>
		</tr>';
	}
    }
    else
    {
	$template_custom_vars = '<tr class="alt1"><td colspan="4">{_no_results_found}</td></tr>';
    }

    // #### template settings ##############################################
    $global_templatesettings = $ilance->admincp->construct_admin_input('template', $ilpage['styles']);

    $pprint_array = array ('template_custom_vars','styles_table', 'products_pulldown', 'prevnext', 'elements_pulldown', 'type', 'styleid', 'global_templatesettings', 'styleid', 'language_pulldown', 'defaultstyle', 'styles_pulldown', 'template_variables', 'after_update_return_to_page');

    ($apihook = $ilance->api('admincp_templates_end')) ? eval($apihook) : false;

    $ilance->template->fetch('main', 'styles.html', 1);
    $ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
    $ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
    $ilance->template->parse_if_blocks('main');
    $ilance->template->pprint('main', $pprint_array);
    exit();
}
else
{
    refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
    exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>