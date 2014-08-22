<?php
if (!defined('LOCATION') OR defined('LOCATION') AND LOCATION != 'admin')
{
	echo 'This script cannot be parsed indirectly.';
	exit();
}

// #### UPDATE SETTINGS ##################################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fbbridge-settings')
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "fbbridge_configuration
		SET value = '" . intval($ilance->GPC['enabled']) . "' 
		WHERE name = 'enabled'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "fbbridge_configuration
		SET value = '" . $ilance->db->escape_string($ilance->GPC['appId']) . "' 
		WHERE name = 'appId'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "fbbridge_configuration
		SET value = '" . $ilance->db->escape_string($ilance->GPC['secret']) . "' 
		WHERE name = 'secret'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "fbbridge_configuration
		SET value = '" . $ilance->db->escape_string($ilance->GPC['fbregister']) . "' 
		WHERE name = 'fbregister'
	");
	
	print_action_success("Your Settings have been saved.", $_SERVER['PHP_SELF'] . '?module=fbbridge');
	exit();
}
else
{
	print_action_failed('Your Facebook Bridging System is not enabled. Please enable the system.', $_SERVER['PHP_SELF'] . '?module=fbbridge');
	exit();
}
?>