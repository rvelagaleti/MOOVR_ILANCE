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
		'inline',
		'jquery',
		'tabfx',
		'wysiwyg',
		'ckeditor'
	),
	'footer' => array(
		'tooltip',
		'cron'
	)
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');

($apihook = $ilance->api('admincp_components_start')) ? eval($apihook) : false;

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['components'] => $ilcrumbs[$ilpage['components']]);
if (($v3nav = $ilance->cache->fetch("print_admincp_nav_components")) === false)
{
	$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['components']);
	$ilance->cache->store("print_admincp_nav_components", $v3nav);
}
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'components' AND isset($ilance->GPC['module']) AND isset($ilance->GPC['subcmd']) AND !isset($ilance->GPC['external']))
        {
		$ilmodule = '';
		$ilmodule = mb_strtolower(trim($ilance->GPC['module']));
		$staticphrasegroups[] = $ilmodule;
		$phrase['groups'] = $staticphrasegroups;
		$phrase = $ilance->language->init_phrases();
		include(DIR_ADMIN . DIR_ADMIN_ADDONS_NAME . '/' . $ilmodule . '.mod.inc' . $ilconfig['globalsecurity_extensionmime']);
		exit();	
	}
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'components' AND isset($ilance->GPC['module']) AND isset($ilance->GPC['subcmd']) AND !isset($ilance->GPC['external']))
        {
		$ilmodule = '';
		$ilmodule = mb_strtolower(trim($ilance->GPC['module']));
		$staticphrasegroups[] = $ilmodule;
		$phrase['groups'] = $staticphrasegroups;
		$phrase = $ilance->language->init_phrases();
		include(DIR_ADMIN . DIR_ADMIN_ADDONS_NAME . '/' . $ilmodule . '.mod.inc' . $ilconfig['globalsecurity_extensionmime']);
		exit();
	}
	// #### ADDON UPGRADE MANAGER ##########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'upgrade' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'mod')
	{
		if ($show['ADMINCP_TEST_MODE'] == false)
		{
			while (list($key, $value) = each($_FILES)) $GLOBALS["$key"] = $value;
			foreach ($_FILES as $key => $value)
			{
				$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
				foreach ($value as $ext => $value2)
				{
					$key2 = $key . '_' . $ext;
					$ilance->GPC["$key2"] = $GLOBALS["$key2"] = $value2;
				}
			}
			$xml = file_get_contents($xml_file);
			$movephrases = (isset($ilance->GPC['movephrases']) AND $ilance->GPC['movephrases']) ? 1 : 0;
			$updatephrases = (isset($ilance->GPC['updatephrases']) AND $ilance->GPC['updatephrases']) ? 1 : 0;
			$updateemails = (isset($ilance->GPC['updateemails']) AND $ilance->GPC['updateemails']) ? 1 : 0;
			$ilance->admincp_products->upgrade($xml, $movephrases, $updatephrases, $updateemails);
		}
		else
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
	}
	
	// #### UNINSTALL ADDON MANAGEMENT #####################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'uninstall')
        {
		$area_title = '{_product_addon_uninstallation}';
		$page_title = SITE_NAME . ' - {_product_addon_uninstallation}';
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'] . '?cmd=install', $_SESSION['ilancedata']['user']['slng']);
		
		($apihook = $ilance->api('admincp_uninstall_components_management')) ? eval($apihook) : false;
		
		if ($show['ADMINCP_TEST_MODE'] == false)
		{
			if (isset($ilance->GPC['modulegroup']) AND !empty($ilance->GPC['modulegroup']))
			{
				$extra = '';
				if (isset($ilance->GPC['showfiles']) AND $ilance->GPC['showfiles'])
				{
					$files = $ilance->admincp_products->print_file_dependencies($ilance->GPC['modulegroup']);
					$extra = '<br /><br />Please remove <span style="color:blue">found</span> files below:<br /><br />' . $files;
				}
				if ($ilance->admincp_products->uninstall($ilance->GPC['modulegroup']))
				{
					print_action_success('{_addon_product_was_uninstalled_from_your_control_panel}' . $extra, $ilpage['components']);
					exit();
				}
				else
				{
					print_action_failed('{_addon_product_could_not_be_uninstalled_using_the_uninstall_manager}', $ilpage['components']);
					exit();
				}
			}
		}
		else
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
	}
	// #### ADDON INSTALL MANAGEMENT #######################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'install')
        {
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'mod')
		{
			if ($show['ADMINCP_TEST_MODE'] == false)
			{
				while (list($key, $value) = each($_FILES)) $GLOBALS["$key"] = $value;
				foreach ($_FILES as $key => $value)
				{
					$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
					foreach ($value as $ext => $value2)
					{
						$key2 = $key . '_' . $ext;
						$ilance->GPC["$key2"] = $GLOBALS["$key2"] = $value2;
					}
				}
				$ignoreversion = (isset($ilance->GPC['ignoreversion']) AND $ilance->GPC['ignoreversion']) ? intval($ilance->GPC['ignoreversion']) : 0;
				$movephrases = (isset($ilance->GPC['movephrases']) AND $ilance->GPC['movephrases']) ? 1 : 0;
				$updatephrases = (isset($ilance->GPC['updatephrases']) AND $ilance->GPC['updatephrases']) ? 1 : 0;
				$xml = file_get_contents($xml_file);
				$ilance->admincp_products->install($xml, $ignoreversion, $movephrases, $updatephrases, 1);
			}
			else
			{
				print_action_failed('{_demo_mode_only}', $ilpage['components']);
				exit();
			}
		}
		else
		{
			$area_title = '{_addon_installation_menu}';
			$page_title = SITE_NAME . ' - {_addon_installation_menu}';
			
			$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'] . '?cmd=install', $_SESSION['ilancedata']['user']['slng']);
			
			($apihook = $ilance->api('admincp_install_components_management')) ? eval($apihook) : false;
			
			$modulespulldown = $ilance->admincp_products->modules_pulldown();
			$pprint_array = array('modulespulldown','module','input_style');
			
			($apihook = $ilance->api('admincp_components_install_end')) ? eval($apihook) : false;
			
			$ilance->template->fetch('main', 'components_install.html', 1);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	// #### APP STORE ######################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'appstore')
        {
		$area_title = '{_ilance_app_store}';
		$page_title = SITE_NAME . ' - {_ilance_app_store}';
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'] . '?cmd=appstore', $_SESSION['ilancedata']['user']['slng']);
                
		($apihook = $ilance->api('admincp_appstore_components_management')) ? eval($apihook) : false;
                
                $pprint_array = array('modulespulldown','module','input_style');
                
                ($apihook = $ilance->api('admincp_appstore_components_management_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'components_appstore.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### FORUM ##########################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'forum')
        {
		$area_title = '{_ilance_forum}';
		$page_title = SITE_NAME . ' - {_ilance_forum}';
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'] . '?cmd=forum', $_SESSION['ilancedata']['user']['slng']);
                
		($apihook = $ilance->api('admincp_appstore_components_management')) ? eval($apihook) : false;
                
                $pprint_array = array('modulespulldown','module','input_style');
                
                ($apihook = $ilance->api('admincp_appstore_components_management_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'components_forum.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
        {
		$area_title = '{_product_addons_and_plugins}';
		$page_title = SITE_NAME . ' - {_product_addons_and_plugins}';
                
		($apihook = $ilance->api('admincp_components_management')) ? eval($apihook) : false;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'], $_SESSION['ilancedata']['user']['slng']);
		
		$sqlmodgroup = $ilance->db->query("
                        SELECT modulegroup, modulename, version, versioncheckurl, developer
                        FROM " . DB_PREFIX . "modules_group
                ");
		while ($modgroupres = $ilance->db->fetch_array($sqlmodgroup, DB_ASSOC))
		{
			$modulegroup[] = $modgroupres;
		}
		unset($modgroupres);
		if (isset($ilance->GPC['external']) AND $ilance->GPC['external'])
		{
			$module = '';
			if (isset($ilance->GPC['module']))
			{
				$module = trim($ilance->GPC['module']);
				$staticphrasegroups[] = $module;
				$phrase['groups'] = $staticphrasegroups;
				$phrase = $ilance->language->init_phrases();
			}
			$subcmd = '';
			if (isset($ilance->GPC['subcmd']))
			{
				$subcmd = $ilance->GPC['subcmd'];
			}
			$where = "WHERE modulegroup = 'unknown'";
			if (!empty($module))
			{
				$where = "WHERE modulegroup = '" . $ilance->db->escape_string($module) . "'";
			}
			$sqlmodgroups = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, modulegroup, modulename, folder, configtable, installcode, uninstallcode, version, versioncheckurl, url, developer
                                FROM " . DB_PREFIX . "modules_group
                                $where
                        ");
			while ($modgroupsres = $ilance->db->fetch_array($sqlmodgroups, DB_ASSOC))
			{
				// construct tabs for this addon
				$sql = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, parentkey, tab, modulegroup, template
					FROM " . DB_PREFIX . "modules
					WHERE modulegroup = '" . $modgroupsres['modulegroup'] . "'
						AND sort = '-1'
						AND subcmd = '" . $ilance->db->escape_string($subcmd) . "'
					ORDER BY sort ASC");
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$res['moduletab'] = '';
						$res['moduletab'] .= '<div class="tab-pane" style="width:100%" id="' . $modgroupsres['modulegroup'] . '"><div class="tab-page" id="' . $modgroupsres['modulegroup'] . '"><h2 class="tab">' . stripslashes($res['tab']) . '</h2>' . parse_php_in_html($res['template']) . '</div></div>';
						$GLOBALS['moduletabs' . $modgroupsres['id']][] = $res;
					}
				}
				$modulegroups[] = $modgroupsres;
			}
			unset($modgroupsres);
		}
		else
		{
			$module = '';
			if (isset($ilance->GPC['module']) AND $ilance->GPC['module'] != '')
			{
				$module = trim($ilance->GPC['module']);
				$staticphrasegroups[] = $module;
				$phrase['groups'] = $staticphrasegroups;
			}
			$where = "WHERE modulegroup = 'unknown'";
			if (!empty($module))
			{
				$where = "WHERE modulegroup = '" . $ilance->db->escape_string($module) . "'";
			}
			// load requested module group
			$sqlmodgroups = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, modulegroup, modulename, folder, configtable, installcode, uninstallcode, version, versioncheckurl, url, developer
                                FROM " . DB_PREFIX . "modules_group
                                $where
                        ");
			while ($modgroupsres = $ilance->db->fetch_array($sqlmodgroups, DB_ASSOC))
			{
				$area_title = '{_product_addons_and_plugins}<div class="smaller">' . $modgroupsres['modulename'] . '</div>';
				$page_title = SITE_NAME . ' - {_product_addons_and_plugins} - ' . $modgroupsres['modulename'];
				$sql = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, modulegroup, tab, template
                                        FROM " . DB_PREFIX . "modules
					WHERE modulegroup = '" . $modgroupsres['modulegroup'] . "'
						AND sort != '-1'
					ORDER BY sort ASC
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
                                        $extra = $ilance->admincp_products->print_file_dependencies($modgroupsres['modulegroup']);
                                        // before we include the core products installed, let's find out if we have the necessary file sturucture
                                        if (!file_exists(DIR_API . 'class.' . $modgroupsres['modulegroup'] . '.inc.php'))
                                        {
                                                print_action_failed('{_the_main_class_for_this_product_could_not_be_found}' . ' <strong>./functions/api/class.' . $modgroupsres['modulegroup'] . '.inc.php</strong>.  ' . '{_please_remember_after_you_install_the_product_using_the_admincp_interface_you_must}' . '<br /><br />' . $extra, 'components.php');
                                        }
                                        if (!file_exists(DIR_XML . 'plugin_' . $modgroupsres['modulegroup'] . '.xml'))
                                        {
                                                print_action_failed('{_the_main_xml_framework_for_this_product_could_not_be_found}'.' <strong>./functions/xml/plugin_' . $modgroupsres['modulegroup'] . '.xml</strong>.  ' . '{_please_remember_after_you_install_the_product_using_the_admincp_interface_you_must}' . '<br /><br />' . $extra, 'components.php');
                                        }
					while ($res = $ilance->db->fetch_array($sql))
					{
						$res['moduletab'] = '<!-- begin pane --><div class="tab-page" id="' . stripslashes($modgroupsres['modulegroup']) . '-' . $modgroupsres['id'] . '"><h2 class="tab">' . stripslashes($res['tab']) . '</h2>' . parse_php_in_html($res['template']) . '</div><!-- /begin pane -->';
						$GLOBALS['moduletabs' . $modgroupsres['id']][] = $res;
					}
				}
				$modulegroups[] = $modgroupsres;
			}
			unset($modgroupsres);
		}
		$cbaddons = '';
                $row_count = 0;
		if (isset($modulegroup) AND is_array($modulegroup))
		{
			foreach ($modulegroup AS $key => $addon)
			{
				$class = ($row_count % 2) ? 'alt1' : 'alt1';
				if (isset($ilance->GPC['module']) AND $ilance->GPC['module'] == $addon['modulegroup'])
				{
					$cbaddons .= '<tr class="alt2_top" valign="top">
	<td><span class="blue"><a href="' . $ilpage['components'] . '?module=' . $addon['modulegroup'] . '"><strong>' . $addon['modulename'] . '</strong></a></span></td>
	<td><span class="smaller">' . $addon['version'] . '</span></td>
	<td><span class="smaller">' . $ilance->latest_addon_version($addon['versioncheckurl']) . '</span></td>
	<td><span class="smaller blue">' . $ilance->addon_phrase_count($addon['modulegroup']) . '</span></td>
	<td><span class="smaller blue">' . $ilance->addon_css_count($addon['modulegroup']) . '</span></td>
	<td><span class="smaller blue">' . $ilance->addon_email_count($addon['modulegroup']) . '</span></td>
	<td><span class="smaller blue">' . $ilance->addon_task_count($addon['modulegroup']) . '</span></td>
	<td><span class="smaller litegray">' . stripslashes($addon['developer']) . '</span></td>
</tr>';
				}
				else
				{
					$cbaddons .= '<tr class="' . $class . '">
	<td><span class="blue"><a href="' . $ilpage['components'] . '?module=' . $addon['modulegroup'] . '">' . $addon['modulename'] . '</a></span></td>
	<td><span class="smaller">' . $addon['version'] . '</span></td>
	<td><span class="smaller">' . $ilance->latest_addon_version($addon['versioncheckurl']) . '</span></td>
	<td><span class="smaller blue">' . $ilance->addon_phrase_count($addon['modulegroup']) . '</span></td>
	<td><span class="smaller blue">' . $ilance->addon_css_count($addon['modulegroup']) . '</span></td>
	<td><span class="smaller blue">' . $ilance->addon_email_count($addon['modulegroup']) . '</span></td>
	<td><span class="smaller blue">' . $ilance->addon_task_count($addon['modulegroup']) . '</span></td>
	<td><span class="smaller litegray">' . stripslashes($addon['developer']) . '</span></td>
</tr>';
				}
				$row_count++;
			}
		}
                if ($row_count == 0)
                {
                        $cbaddons .= '<tr><td colspan="7" align="center">' . '{_no_products_or_plugins_found}' . '</td></tr>';               
                }
                $pprint_array = array('cbaddons','page','catid','module','input_style');
		
                ($apihook = $ilance->api('admincp_components_end')) ? eval($apihook) : false;
                
		$ilance->template->fetch('main', 'components.html', 1);
		$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
		$ilance->template->parse_loop('main', 'modulegroups');
		if (!isset($modulegroups))
		{
			$modulegroups = array();
		}
		@reset($modulegroups);
		while ($i = @each($modulegroups))
		{
			$ilance->template->parse_loop('main', 'moduletabs' . $i['value']['id']);
		}
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
        }
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