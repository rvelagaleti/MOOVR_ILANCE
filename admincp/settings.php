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
		'tabfx',
		'jquery',
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
require_once('./../functions/core/functions_wysiwyg.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['settings'] => $ilcrumbs[$ilpage['settings']]);
if (($v3nav = $ilance->cache->fetch("print_admincp_nav_settings")) === false)
{
	$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['settings']);
	$ilance->cache->store("print_admincp_nav_settings", $v3nav);
}
if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
// #### CUSTOM REGISTRATION QUESTIONS MANAGEMENT ###############################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'registration')
{
	include_once(DIR_ADMIN . 'settings_registration.php');
}
// #### FEEDBACK CRITERIA MANAGEMENT ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'feedback')
{
	include_once(DIR_ADMIN . 'settings_feedback.php');
}
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'subscriptions')
{
	include_once(DIR_ADMIN . 'settings_membership.php');
}
// #### MARKETPLACE HANDLER ####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'marketplace')
{
	include_once(DIR_ADMIN . 'settings_marketplace.php');
}
// #### PAYMENT MODULES ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'paymodules')
{
	include_once(DIR_ADMIN . 'settings_paymodules.php');
}
// #### EMAIL TEMPLATES ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'emailtemplates')
{        
	include_once(DIR_ADMIN . 'settings_emailtemplates.php');
}
// #### MAINTENANCE MODE #######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'maintenance')
{
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=maintenance', $_SESSION['ilancedata']['user']['slng']);
	$area_title = '{_maintenance_mode_menu}';
	$page_title = SITE_NAME . ' - {_maintenance_mode_menu}';

	($apihook = $ilance->api('admincp_maintenance_settings')) ? eval($apihook) : false;

	$configuration_input = $ilance->admincp->construct_admin_input('maintenance', $ilpage['settings'] . '?cmd=maintenance');

	($apihook = $ilance->api('admincp_maintenance_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'maintenance.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', array('configuration_input'));
	exit();
	
}
// #### SCHEDULED TASKS ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'automation')
{
	include_once(DIR_ADMIN . 'settings_automation.php');
}
// #### UPDATE GLOBAL SETTINGS HANLDER #########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'globalupdate')
{
	$notice = '';
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-config-settings')
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		foreach ($ilance->GPC['config'] AS $varname => $value)
		{
			if ($varname == 'attachment_dbstorage')
			{
				if (isset($value) AND $value == 0 AND $ilconfig['attachment_dbstorage'])
				{
					$notice = $ilance->attachment_tools->move_attachments_to_filepath();
				}
				else if (isset($value) AND $value == 1 AND $ilconfig['attachment_dbstorage'] == 0)
				{
					$notice = $ilance->attachment_tools->move_attachments_to_database(true);
				}
			}
			else if ($varname == 'maxshipservices')
			{
				if ($value > 10)
				{
					$value = 10;
				}
			}
			else if ($varname == 'shipping_regions')
			{
			    $value = serialize($value);
			}
			else if ($varname == 'globalauctionsettings_auctionstypeenabled')
			{
				if ($value == 'service')
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '0' WHERE name = 'globalauctionsettings_productauctionsenabled'");
					$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '1' WHERE name = 'globalauctionsettings_serviceauctionsenabled'");
				}
				else
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '1' WHERE name = 'globalauctionsettings_productauctionsenabled'");
					$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '0' WHERE name = 'globalauctionsettings_serviceauctionsenabled'");
				}
			}
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . $ilance->db->escape_string($value) . "',
				sort = '" . intval($ilance->GPC['sort'][$varname]) . "'
				WHERE name = '" . $ilance->db->escape_string($varname) . "'
			");
			$sql = $ilance->db->query("
				SELECT value, inputname
				FROM " . DB_PREFIX . "configuration
				WHERE name = '" . $ilance->db->escape_string($varname) . "'
					AND inputtype = 'pulldown'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$writepulldown = '';
				if ($res['inputname'] == 'currencyrates')
				{
					$writepulldown = $ilance->currency->pulldown('admin', $varname);
				}
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET inputcode = '" . $ilance->db->escape_string($writepulldown) . "'
					WHERE name = '" . $ilance->db->escape_string($varname) . "'
				");
			}
			else
			{
				if (isset($ilance->GPC['config']['globalfilters_vulgarpostfilterlist']))
				{
					$trimed_str = trim($ilance->GPC['config']['globalfilters_vulgarpostfilterlist']);
					if (substr($trimed_str, -1) == ',')
					{
						print_action_failed('{_do_not_include_comma_at_the_end}', $ilpage['settings']);
					}
				}
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET value = '" . $ilance->db->escape_string($value) . "'
					WHERE name = '" . $ilance->db->escape_string($varname) . "'
				");
			}
		}
		$ilance->cachecore->delete("ilconfig");
	}
	print_action_success('{_configuration_settings_have_been_saved_to_the_database}<br /><br />' . $notice, $ilance->GPC['return']);
	exit();
}
// #### PAYMENT MODULES UPDATE HANDLER #########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'paymodulesupdate')
{
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-config-settings')
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		foreach ($ilance->GPC['config'] AS $key => $value)
		{
			// are we updating the payment pulldown menu?
			if ($key == 'use_internal_gateway')
			{
				$sql = $ilance->db->query("
					SELECT id, value, inputname
					FROM " . DB_PREFIX . "payment_configuration
					WHERE name = '" . $ilance->db->escape_string($key) . "'
						AND inputtype = 'pulldown'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					if ($res['inputname'] == 'defaultgateway')
					{
						$writepulldown = $ilance->admincp_paymodules->default_gateway_pulldown($value, $key);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "payment_configuration
							SET inputcode = '" . $ilance->db->escape_string($writepulldown) . "',
							value = '" . $ilance->db->escape_string($value) . "'
							WHERE name = '" . $ilance->db->escape_string($key) . "'
								AND inputtype = 'pulldown'
						");
					}
				}
			}
			else
			{
				if (isset($key) AND $key > 0)
				{
					if($ilance->db->fetch_field(DB_PREFIX . "payment_configuration", "id = '" . intval($key) . "'", "inputtype", "1") == 'int')
					{
						$value = floatval($value);
					}
					if($ilance->db->fetch_field(DB_PREFIX . "payment_configuration", "id = '" . intval($key) . "'", "name", "1") == 'creditcard_authentication' AND $value == 0)
					{
					    $ilance->db->query("UPDATE " . DB_PREFIX . "creditcards
						SET	 authorized = 'yes' WHERE authorized = 'no'");
					}
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "payment_configuration
						SET value = '" . $ilance->db->escape_string(trim($value)) . "',
						sort = '" . intval($ilance->GPC['sort'][$key]) . "'
						WHERE id = '" . intval($key) . "'
					");
				}
			}

		}
	}
	print_action_success('{_payment_configuration_settings_saved}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE -- TERMS PRIVACY ABOUT REGISTRATIONTERMS -- EDITOR ########################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'save_cms_pages' AND !empty($ilance->GPC['subcmd']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$accepted = array('terms', 'privacy', 'about', 'registrationterms', 'news');
	$key = $ilance->GPC['subcmd'];
	if (in_array($key, $accepted) AND isset($ilance->GPC[$key]))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "cms
			SET " . $ilance->db->escape_string($key) . " = '" . $ilance->db->escape_string($ilance->GPC[$key]) . "'
		");
		print_action_success('{_configuration_settings_have_been_saved_to_the_database}<br /><br />', $ilpage['settings']);
		exit();
	}
	else
	{
		print_action_failed('{_please_fill_all_fields}', $ilpage['settings']);
		exit();
	}
}
// #### UPDATE HTACCESS FROM EDITOR ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'savehtaccess')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if (isset($ilance->GPC['htaccess']) AND !empty($ilance->GPC['htaccess']))
	{
		if (file_exists(DIR_SERVER_ROOT . '.htaccess'))
		{
			if (is_writable(DIR_SERVER_ROOT . '.htaccess'))
			{
				if (file_put_contents(DIR_SERVER_ROOT . '.htaccess', $ilance->GPC['htaccess']))
				{
					print_action_success('The .htaccess file was successfully updated.  Please remember to CHMOD the file permissions back to 644.<br /><br />', $ilpage['settings']);
					exit();
				}
				else
				{
					print_action_failed('The .htaccess file could not be saved.  Please CHMOD file permissions to 777 and try again.', $ilpage['settings']);
					exit();
				}
			}
			else
			{
				print_action_failed('The .htaccess file could not be saved.  Please CHMOD file permissions to 777 and try again.', $ilpage['settings']);
				exit();
			}
		}
		else
		{
			print_action_failed('The .htaccess file could not found.  Please upload this file (or a blank file called .htaccess) in the root of your folder and try again.', $ilpage['settings']);
			exit();
		}
	}
	else
	{
		print_action_failed('The .htaccess file was not saved.', $ilpage['settings']);
		exit();
	}
}
// #### UPDATE HTACCESS FROM EDITOR ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'reverthtaccess')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if (file_exists(DIR_CORE . 'functions_htaccess_original.txt'))
	{
		if (is_writable(DIR_SERVER_ROOT . '.htaccess'))
		{
			$original = file_get_contents(DIR_CORE . 'functions_htaccess_original.txt');
			if (file_put_contents(DIR_SERVER_ROOT . '.htaccess', $original))
			{
				print_action_success('The .htaccess file was successfully reverted.  Please remember to CHMOD the file permissions for .htaccess back to 644.', $ilpage['settings']);
				exit();
			}
			else
			{
				print_action_failed('The .htaccess file could not be reverted.  Please CHMOD .htaccess file permissions to 777 and try again.', $ilpage['settings']);
				exit();
			}
		}
		else
		{
			print_action_failed('The .htaccess file could not be reverted.  Please CHMOD .htaccess file permissions to 777 and try again.', $ilpage['settings']);
			exit();
		}
	}
	else
	{
		print_action_failed('The original .htaccess file could not be found.  Revert not completed.', $ilpage['settings']);
		exit();
	}
}
else
{
	$area_title = '{_global_configuration_menu}';
	$page_title = SITE_NAME . ' - {_global_configuration_menu}';
	
	($apihook = $ilance->api('admincp_global_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=global', $_SESSION['ilancedata']['user']['slng']);
	$global_securitysettings = $ilance->admincp->construct_admin_input('globalsecurity', $ilpage['settings'] . '?cmd=global');
	$global_filtersettings = $ilance->admincp->construct_admin_input('globalfilters', $ilpage['settings'] . '?cmd=global');
	$global_filterresults = $ilance->admincp->construct_admin_input('globalfilterresults', $ilpage['settings'] . '?cmd=global');
	$global_tab_visibility = $ilance->admincp->construct_admin_input('globaltabvisibility', $ilpage['settings'] . '?cmd=global');
	$global_serverdistanceapi = $ilance->admincp->construct_admin_input('globalserverdistanceapi', $ilpage['settings'] . '?cmd=global');
	$global_serverlocale = $ilance->admincp->construct_admin_input('globalserverlocale', $ilpage['settings'] . '?cmd=global');
	$global_serversmtp = $ilance->admincp->construct_admin_input('globalserversmtp', $ilpage['settings'] . '?cmd=global');
	$global_servercache = $ilance->admincp->construct_admin_input('globalservercache', $ilpage['settings'] . '?cmd=global');
	$global_serversettings = $ilance->admincp->construct_admin_input('globalserversettings', $ilpage['settings'] . '?cmd=global');
	$global_serversession = $ilance->admincp->construct_admin_input('globalserversession', $ilpage['settings'] . '?cmd=global');
	$global_search = $ilance->admincp->construct_admin_input('search', $ilpage['settings'] . '?cmd=global');
	$global_seo = $ilance->admincp->construct_admin_input('globalseo', $ilpage['settings'] . '?cmd=global');
	$global_htaccess = file_get_contents(DIR_SERVER_ROOT . '.htaccess');
	$global_wysiwygsettings = $ilance->admincp->construct_admin_input('wysiwygsettings', $ilpage['settings'] . '?cmd=global');
	$global_wysiwygeditor = $ilance->admincp->construct_cms_pages();
	
	// #### distance api installed countries list ##################
	$installedcountries = $ilance->distance->fetch_installed_countries();
	$pprint_array = array('global_htaccess','global_serversession','global_servercache','global_serversmtp','global_wysiwygeditor','global_wysiwygsettings','global_tab_visibility','global_seo','global_metatags','global_search','global_serversettings','global_serverlocale','global_serverdistanceapi','global_securitysettings','global_filtersettings','global_filterresults');
	
	($apihook = $ilance->api('admincp_global_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'global.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'clientnav_language_tabs');
	$ilance->template->parse_loop('main', 'installedcountries');
	if (!isset($clientnav_language_tabs))
	{
		$clientnav_language_tabs = array();
	}
	@reset($clientnav_language_tabs);
	while ($x = @each($clientnav_language_tabs))
	{
		$ilance->template->parse_loop('main', 'clientnav_languageid' . $x['value']['languageid']);
	}
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
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