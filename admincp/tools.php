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
define('AREA', 'tools');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[tools]" => $ilcrumbs["$ilpage[tools]"]);
$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['tools']);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$area_title = '{_tools}';
	$page_title = SITE_NAME . ' - {_tools}';
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['tools'], $ilpage['tools'], $_SESSION['ilancedata']['user']['slng']);
	// #### EXPORT CONFIGURATION ############################################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'export')
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		$area_title = '{_export} {_configuration}';
		$page_title = SITE_NAME . ' - ' . '{_export} {_configuration}';
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['tools'], $ilpage['tools'], $_SESSION['ilancedata']['user']['slng']);
		$ilance->admincp_importexport = construct_object('api.admincp_importexport');
		$ilance->admincp_importexport->export('configuration', 'admincp', 0, '', false, 0, '1', 'ilance');
		exit();
	} 
	// #### IMPORT CONFIGURATION ############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'import')
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		$area_title = '{_import} {_configuration}';
		$page_title = SITE_NAME . ' - ' . '{_import} {_configuration}';
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['tools'], $ilpage['tools'], $_SESSION['ilancedata']['user']['slng']);
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update' AND isset($ilance->GPC['xml']) AND is_array($ilance->GPC['xml']))
		{
			foreach ($ilance->GPC['xml'] AS $key => $value)
			{
				if ($key != 'current_sql_version')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "configuration
						SET value = '" . $ilance->db->escape_string($value) . "'
						WHERE name = '" . $ilance->db->escape_string($key) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
			}	
			print_action_success('{_configuration_importation_success}', $ilance->GPC['return']);
			exit();		
		}
		while (list($key, $value) = each($_FILES)) $GLOBALS["$key"] = $value;  
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
		$html = '';
		$i = 0;
		list($html, $i) = $ilance->admincp_importexport->import('configuration', 'admincp', $xml, false, '0', '0', '0');
		$show['preview'] = true;
		if ($i > 0)
		{
			$show['can_update'] = true;
		}
		else 
		{
			print_action_failed('{_nothing_to_do}', $ilpage['tools']);
			exit();
		}
	}
	// #### FIND ORPHAN PHRASES #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'orphan')
	{
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['tools'], $ilpage['tools'] . '?cmd=orphan', $_SESSION['ilancedata']['user']['slng']);
		if (isset($ilance->GPC['type']) AND $ilance->GPC['type'] == 'phrase')
		{
			$area_title = '{_find} {_orphan} {_phrases}';
			$page_title = SITE_NAME . ' - {_find} {_orphans} {_phrases}';
			$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['tools'], $ilpage['tools'] . '?cmd=orphan&type=phrase', $_SESSION['ilancedata']['user']['slng']);
			$ilance->admincp_find_orphan = construct_object('api.admincp_find_orphan');
			$orphan_phrases_table = $orphan_phrases_text_area = '';
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'find')
			{
				$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['tools'], $ilpage['tools'] . '?cmd=orphan&type=phrase&subcmd=find', $_SESSION['ilancedata']['user']['slng']);
				$show['results'] = true;
				$orphan_phrases_text_area .= $ilance->admincp_find_orphan->find_phrase(DIR_SERVER_ROOT);
				$orphan_phrases_table .= "{_total_number_of_phrases_found}: <strong>" . $ilance->admincp_find_orphan->totalphrases . "</strong>, {_total_number_of_orphan_phrases_found}: <strong>" . $ilance->admincp_find_orphan->orphanphrases . "</strong>";
				if ($ilance->admincp_find_orphan->orphanphrases > 0)
				{
					$show['can_delete'] = true;
				}
				else 
				{
					$show['no_results'] = true;
				}
			}
			else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'delete_all')
			{
				$orphan_phrases_text_area = isset($ilance->GPC['orphan_phrases_txt']) ? $ilance->GPC['orphan_phrases_txt'] : '';
				$arr = explode("\n", $orphan_phrases_text_area);
				if (is_array($arr))
				{
					foreach ($arr as $key => $value)
					{
						if (!empty($value))
						{
							$ilance->db->query($value, 0, null, __FILE__, __LINE__);
						}
					}
				}
				print_action_success('{_configuration_importation_success}', $ilpage['tools'] . '?cmd=orphan_phrases#find');
				exit();		
			}
		}
		else if (isset($ilance->GPC['type']) AND $ilance->GPC['type'] == 'email')
		{
			$area_title = '{_find} {_orphan} {_emails}';
			$page_title = SITE_NAME . ' - ' . '{_find} {_orphans} {_emails}';
			$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['tools'], $ilpage['tools'] . '?cmd=orphan&type=email', $_SESSION['ilancedata']['user']['slng']);
			$ilance->admincp_find_orphan = construct_object('api.admincp_find_orphan');
			$orphan_emails_table = $orphan_emails_text_area = '';
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'find')
			{
				$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['tools'], $ilpage['tools'] . '?cmd=orphan&type=email&subcmd=find', $_SESSION['ilancedata']['user']['slng']);
				$show['results_emails'] = true;
				$orphan_emails_text_area .= $ilance->admincp_find_orphan->find_emailtemplate(DIR_SERVER_ROOT);
				$orphan_emails_table .= "{_total_number_of_email_templates_found}: <strong>" . $ilance->admincp_find_orphan->totalphrases . "</strong>, {_total_number_of_orphan_email_templates_found}: <strong>" . $ilance->admincp_find_orphan->orphanphrases . "</strong>";
				if ($ilance->admincp_find_orphan->orphanphrases > 0)
				{
					$show['can_delete_emails'] = true;
				}
				else 
				{
					$show['no_results_emails'] = true;
				}
			}
			else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'delete_all')
			{
				$orphan_emails_text_area = isset($ilance->GPC['orphan_emails_txt']) ? $ilance->GPC['orphan_emails_txt'] : '';
				$arr = explode("\n", $orphan_emails_text_area);
				if (is_array($arr))
				{
					foreach ($arr as $key => $value)
					{
						if (!empty($value))
						{
							$ilance->db->query($value, 0, null, __FILE__, __LINE__);
						}
					}
				}
				print_action_success('{_configuration_importation_success}', $ilpage['tools'] . '?cmd=orphan&type=email');
				exit();		
			}
		}
		
		($apihook = $ilance->api('admincp_tools_management')) ? eval($apihook) : false;
	
		$pprint_array = array('orphan_emails_text_area','orphan_emails_table','orphan_phrases_text_area','orphan_phrases_table');
	    
		($apihook = $ilance->api('admincp_tools_end')) ? eval($apihook) : false;
	
		$ilance->template->fetch('main', 'find_orphan.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	($apihook = $ilance->api('admincp_tools_management')) ? eval($apihook) : false;
	
	$pprint_array = array('html');
    
	($apihook = $ilance->api('admincp_tools_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'tools.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
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