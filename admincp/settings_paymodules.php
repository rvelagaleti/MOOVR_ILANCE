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

$area_title = '{_payment_modules_management}';
$page_title = SITE_NAME . ' - {_payment_modules_management}';
	
($apihook = $ilance->api('can_moderator_access_settings_paymodules')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=paymodules', $_SESSION['ilancedata']['user']['slng']);

// #### INSERT NEW PAYMENT METHOD ##############################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-paytype')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$varname = $ilance->db->escape_string(str_replace(' ', '_', $ilance->GPC['title']));
	$varname = (mb_substr($varname, 0, 1) == '_') ? $varname : '_' . $varname;
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "payment_methods
		(id, title)
		VALUES(
		NULL,
		'" . mb_strtolower($varname) . "')
	", 0, null, __FILE__, __LINE__);
	$query = $ilance->db->query("
		SELECT text_" . $_SESSION['ilancedata']['user']['slng'] . " AS text
		FROM " . DB_PREFIX . "language_phrases
		WHERE varname = '" . mb_strtolower($varname) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($query) == 0)
	{
		$field = $value = '';
		$text = str_replace('_', ' ', $varname);
		$text = ucfirst(ltrim($text));
		$sql_languages = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language", 0, null, __FILE__, __LINE__);
		while ($res = $ilance->db->fetch_array($sql_languages, DB_ASSOC))
		{
			$field .= ", text_" . mb_substr($res['languagecode'], 0, 3);
			$value .= ", '" . $text . "'";
		}
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "language_phrases
			(phraseid, phrasegroup, varname, text_original" . $field . ")
			VALUES(
			NULL,
			'main',
			'" . $ilance->db->escape_string(mb_strtolower($varname)) . "',
			'" . $text . "'
			" . $value . ")
		", 0, null, __FILE__, __LINE__);
	}
	if (isset($ilance->GPC['bulkemail']) AND ($ilance->GPC['bulkemail'] == 1))
	{
	
		$query_seller = $ilance->db->query("
			SELECT u.first_name, u.email,l.languagecode
			FROM " . DB_PREFIX . "projects AS p
			INNER JOIN " . DB_PREFIX . "users AS u ON p.user_id = u.user_id
			INNER JOIN " . DB_PREFIX . "language AS l ON l.languageid = u.languageid
			WHERE p.STATUS = 'open'
			GROUP BY p.user_id
		", 0, null, __FILE__, __LINE__);
			
		$number_seller = $ilance->db->num_rows($query_seller);	
		if ($number_seller > 0)
		{
			$text = str_replace('_', ' ', $varname);
			$text = ucfirst(ltrim($text));
			
			while ($seller_var = $ilance->db->fetch_array($query_seller))
			{
				$ilance->email->get('notify_new_payment_module');
				$ilance->email->mail = $seller_var['email'];
				$ilance->email->slng = mb_substr($seller_var['languagecode'], 0, 3);
				$ilance->email->get('notify_new_payment_module');		
				$ilance->email->set(array(
					'{{firstname}}' => ucwords($seller_var['first_name']),
					'{{site_name}}' => SITE_NAME,					  
					'{{paymenttype}}' => $text,
					'{{http_server}}' => HTTP_SERVER
				));
				
				$ilance->email->send();
			}
		}
	}	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=paymodules');
	exit();
}

// #### REMOVE PAYMENT TYPE ####################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-paytype' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$varname = $ilance->db->fetch_field(DB_PREFIX . "payment_methods", "id = '" . intval($ilance->GPC['id']) . "'", "title");
	$sql = $ilance->db->query("
		SELECT id, paymethod
		FROM " . DB_PREFIX . "projects
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0 AND !empty($varname))
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			if (!empty($res['paymethod']) AND is_serialized($res['paymethod']))
			{
				if (strchr($res['paymethod'], $varname))
				{
					$array = unserialize($res['paymethod']);
					$newarray = $emaillisting = array();
					foreach ($array AS $paymethod)
					{
						if (!empty($paymethod) AND $paymethod != $varname)
						{
							$newarray[] = $paymethod;
						}
					}
					if (count($newarray) > 0)
					{
						$newarray = serialize($newarray);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET paymethod = '" . $ilance->db->escape_string($newarray) . "'
							WHERE id = '" . $res['id'] . "'
						", 0, null, __FILE__, __LINE__);
					}
					else
					{
						// blank payment methods now for listing, email auction owner to update his listing?
						$emaillisting[] = $res['id'];
					}
					
				}
			}
		}
	}
	if (isset($emaillisting) AND count($emaillisting) > 0)
	{
		foreach ($emaillisting AS $projectid)
		{
			// TODO: email listing owners that they should update or risk not getting paid	
		}
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "payment_methods
		WHERE id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=paymodules');
	exit();
}

// #### UPDATE PAYMENT TYPES ###################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-paytypes' AND isset($ilance->GPC['title']) AND is_array($ilance->GPC['title']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	foreach ($ilance->GPC['title'] AS $id => $title)
	{
		if (!empty($title))
		{
			$oldtitle = $ilance->db->fetch_field(DB_PREFIX . "payment_methods", "id = '" . intval($id) . "'", "title");
			if ($title != $oldtitle)
			{
				$sql = $ilance->db->query("
					SELECT id, paymethod
					FROM " . DB_PREFIX . "projects
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0 AND !empty($oldtitle))
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						if (!empty($res['paymethod']) AND is_serialized($res['paymethod']))
						{
							if (strchr($res['paymethod'], $oldtitle))
							{
								$array = unserialize($res['paymethod']);
								$newarray = array();
								foreach ($array AS $paymethod)
								{
									if (!empty($paymethod))
									{
										if ($paymethod == $oldtitle)
										{
											$newarray[] = $title;
										}
										else
										{
											$newarray[] = $paymethod;
										}
									}
								}
								if (count($newarray) > 0)
								{
									$newarray = serialize($newarray);
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects
										SET paymethod = '" . $ilance->db->escape_string($newarray) . "'
										WHERE id = '" . $res['id'] . "'
									", 0, null, __FILE__, __LINE__);
								}
							}
						}
					}
				}
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "payment_methods
				SET title = '" . $ilance->db->escape_string($title) . "'
				WHERE id = '" . intval($id) . "'
			", 0, null, __FILE__, __LINE__);
		}
	}
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=paymodules');
	exit();
}

// #### INSERT NEW PAYMENT METHOD ##############################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-offline-deposit')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$name = isset($ilance->GPC['name']) ? $ilance->db->escape_string($ilance->GPC['name']) : '';
	$number = isset($ilance->GPC['number']) ? $ilance->db->escape_string($ilance->GPC['number']) : '';
	$swift = isset($ilance->GPC['swift']) ? $ilance->db->escape_string($ilance->GPC['swift']) : '';
	$company_name = isset($ilance->GPC['company_name']) ? $ilance->db->escape_string($ilance->GPC['company_name']) : '';
	$company_address = isset($ilance->GPC['company_address']) ? $ilance->db->escape_string($ilance->GPC['company_address']) : '';
	$custom_notes = isset($ilance->GPC['custom_notes']) ? $ilance->db->escape_string($ilance->GPC['custom_notes']) : '';
	$fee = isset($ilance->GPC['fee']) ? $ilance->db->escape_string($ilance->GPC['fee']) : '0.00';
	$visible = isset($ilance->GPC['visible']) ? $ilance->db->escape_string($ilance->GPC['visible']) : '';
	$sort = isset($ilance->GPC['sort']) ? $ilance->db->escape_string($ilance->GPC['sort']) : '';
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "deposit_offline_methods
		(id, name, number, swift, company_name, company_address, custom_notes, fee, visible, sort)
		VALUES(
		NULL,
		'" . $name . "',
		'" . $number . "',
		'" . $swift . "',
		'" . $company_name . "',
		'" . $company_address . "',
		'" . $custom_notes . "',
		'" . $fee . "',
		'" . $visible . "',
		'" . $sort . "')
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=paymodules');
	exit();
}

// #### REMOVE PAYMENT TYPE ####################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-offline-deposit' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "deposit_offline_methods
		WHERE id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=paymodules');
	exit();
}

// #### UPDATE PAYMENT TYPES ###################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-offline-deposit' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$name = isset($ilance->GPC['name']) ? $ilance->db->escape_string($ilance->GPC['name']) : '';
	$number = isset($ilance->GPC['number']) ? $ilance->db->escape_string($ilance->GPC['number']) : '';
	$swift = isset($ilance->GPC['swift']) ? $ilance->db->escape_string($ilance->GPC['swift']) : '';
	$company_name = isset($ilance->GPC['company_name']) ? $ilance->db->escape_string($ilance->GPC['company_name']) : '';
	$company_address = isset($ilance->GPC['company_address']) ? $ilance->db->escape_string($ilance->GPC['company_address']) : '';
	$custom_notes = isset($ilance->GPC['custom_notes']) ? $ilance->db->escape_string($ilance->GPC['custom_notes']) : '';
	$fee = isset($ilance->GPC['fee']) ? $ilance->db->escape_string($ilance->GPC['fee']) : '0.00';
	$visible = isset($ilance->GPC['visible']) ? $ilance->db->escape_string($ilance->GPC['visible']) : '';
	$sort = isset($ilance->GPC['sort']) ? $ilance->db->escape_string($ilance->GPC['sort']) : '';
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "deposit_offline_methods
		SET name = '" . $ilance->db->escape_string($name) . "',
		number = '" . $ilance->db->escape_string($number) . "',
		swift = '" . $ilance->db->escape_string($swift) . "',
		company_name = '" . $ilance->db->escape_string($company_name) . "',
		company_address = '" . $ilance->db->escape_string($company_address) . "',
		custom_notes = '" . $ilance->db->escape_string($custom_notes) . "',
		fee = '" . $ilance->db->escape_string($fee) . "',
		visible = '" . $ilance->db->escape_string($visible) . "',
		sort = '" . $ilance->db->escape_string($sort) . "'
		WHERE id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=paymodules');
	exit();
}

// #### UPDATE PAYMENT TYPES ###################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'edit-offline-deposit' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$sql = $ilance->db->query("
		SELECT id, name, number, swift, sort, visible, company_name, company_address, fee, custom_notes
		FROM " . DB_PREFIX . "deposit_offline_methods
		WHERE id = '" . intval($ilance->GPC['id']) . "' 
		ORDER BY sort ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$row = $ilance->db->fetch_array($sql);
		
		$name = $row['name'];
		$number = $row['number'];
		$swift = $row['swift'];
		$companyname = $row['company_name'];
		$company_address = $row['company_address'];
		$custom_notes = $row['custom_notes'];
		$fee = $row['fee'];
		$visible = $row['visible'];
		$sort = $row['sort'];
	}
	$show['edit'] = true;	
}

// #### CREATE NEW TAX ZONE ####################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'create-taxzone')
{
	$ilance->GPC['invoicetypes'] = (!empty($ilance->GPC['taxtype'])) ? serialize($ilance->GPC['taxtype']) : '';
	if (empty($ilance->GPC['country']))
	{
		print_action_failed('{_you_must_enter_a_tax_zone_country_name_please_retry}', $ilance->GPC['return']);
		exit();
	}
	$countryid = intval($ilance->db->fetch_field(DB_PREFIX . "locations", "location_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($ilance->GPC['country']) . "'", "locationid"));
	if ($countryid == 0)
	{
		print_action_failed('{_there_is_no_country_with_this_name_in_the_system_please_retry}', $ilance->GPC['return']);
		exit();
	}
	if (empty($ilance->GPC['taxlabel']))
	{
		print_action_failed('{_you_must_enter_a_tax_zone_title_name_please_retry}', $ilance->GPC['return']);
		exit();
	}
	if (empty($ilance->GPC['state']))
	{
		$ilance->GPC['state'] = '';
	}
	if (empty($ilance->GPC['city']))
	{
		$ilance->GPC['city'] = '';
	}
	if (empty($ilance->GPC['amount']))
	{
		print_action_failed('{_you_must_enter_a_tax_zone_amount_please_retry}', $ilance->GPC['return']);
		exit();
	}
	$entirecountry = ((isset($ilance->GPC['entirecountry']) AND $ilance->GPC['entirecountry'] == 'true') ? 1 : 0);
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "taxes
		(taxid, taxlabel, state, countryname, countryid, city, amount, invoicetypes, entirecountry)
		VALUES(
		NULL,
		'" . $ilance->db->escape_string($ilance->GPC['taxlabel']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['state']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['country']) . "',
		'" . intval($countryid) . "',
		'" . $ilance->db->escape_string($ilance->GPC['city']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['amount']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['invoicetypes']) . "',
		'" . intval($entirecountry) . "')
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_new_tax_zone_was_successfully_added_to_the_tax_zone_system}', $ilance->GPC['return']);
	exit();
}
	
// #### REMOVE TAX ZONE ########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-taxzone')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "taxes
		WHERE taxid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_the_selected_tax_zone_was_successfully_removed}', $ilpage['settings'] . '?cmd=paymodules');
	exit();
}

// #### UPDATE TAX ZONE ########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-taxzone')
{
	$entirecountry = ((isset($ilance->GPC['entirecountry']) AND $ilance->GPC['entirecountry'] == 'true') ? 1 : 0);
	if ($entirecountry AND empty($ilance->GPC['state']))
	{
		$ilance->GPC['state'] = '';
	}
	if ($entirecountry AND empty($ilance->GPC['city']))
	{
		$ilance->GPC['city'] = '';
	}
	if (!empty($ilance->GPC['taxtype']))
	{
		$ilance->GPC['invoicetypes'] = serialize($ilance->GPC['taxtype']);
	}
	else
	{
		$ilance->GPC['invoicetypes'] = '';
	}
	if (empty($ilance->GPC['country']))
	{
		print_action_failed('{_you_must_enter_a_tax_zone_country_name_please_retry}', $ilance->GPC['return']);
		exit();
	}
	$countryid = (int)$ilance->db->fetch_field(DB_PREFIX . "locations", "location_" . $_SESSION['ilancedata']['user']['slng'] . " = '".$ilance->db->escape_string($ilance->GPC['country'])."'", "locationid");
	if ($countryid == 0)
	{
		print_action_failed('{_there_is_no_country_with_this_name_in_the_system_please_retry}', $ilance->GPC['return']);
		exit();
	}
	if (empty($ilance->GPC['taxlabel']))
	{
		print_action_failed('{_you_must_enter_a_tax_zone_title_name_please_retry}', $ilance->GPC['return']);
		exit();
	}
	if (empty($ilance->GPC['state']) AND $entirecountry == 0)
	{
		print_action_failed('{_you_must_enter_a_tax_zone_state_please_retry}', $ilance->GPC['return']);
		exit();
	}
	if (empty($ilance->GPC['amount']))
	{
		print_action_failed('{_you_must_enter_a_tax_zone_amount_please_retry}', $ilance->GPC['return']);
		exit();
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "taxes
		SET taxlabel = '" . $ilance->db->escape_string($ilance->GPC['taxlabel']) . "',
		state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "',
		countryname = '" . $ilance->db->escape_string($ilance->GPC['country']) . "',
		countryid = '" . intval($countryid) . "',
		city = '" . $ilance->db->escape_string($ilance->GPC['city']) . "',
		amount = '" . $ilance->db->escape_string($ilance->GPC['amount']) . "',
		invoicetypes = '" . $ilance->GPC['invoicetypes'] . "',
		entirecountry = '" . $entirecountry . "'
		WHERE taxid = '" . intval($ilance->GPC['taxid']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_tax_zone_was_successfully_updated_in_the_tax_zone_system}', $ilance->GPC['return']);
	exit();
}

// #### PAYMENT MODULES ########################################################
$paymodules_authnet = $ilance->admincp_paymodules->construct_paymodules_input('authnet', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_bluepay = $ilance->admincp_paymodules->construct_paymodules_input('bluepay', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_plugnpay = $ilance->admincp_paymodules->construct_paymodules_input('plug_n_pay', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_psigate = $ilance->admincp_paymodules->construct_paymodules_input('psigate', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_eway = $ilance->admincp_paymodules->construct_paymodules_input('eway', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_defaultgateway = $ilance->admincp_paymodules->construct_paymodules_input('defaultgateway', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_paypal = $ilance->admincp_paymodules->construct_paymodules_input('paypal', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_cashu = $ilance->admincp_paymodules->construct_paymodules_input('cashu', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_moneybookers = $ilance->admincp_paymodules->construct_paymodules_input('moneybookers', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_check = $ilance->admincp_paymodules->construct_paymodules_input('check', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_bank = $ilance->admincp_paymodules->construct_paymodules_input('bank', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_keys = $ilance->admincp_paymodules->construct_paymodules_input('keys', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_platnosci = $ilance->admincp_paymodules->construct_paymodules_input('platnosci', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_paypal_pro = $ilance->admincp_paymodules->construct_paymodules_input('paypal_pro', $ilpage['settings'] . '?cmd=paymodules');
$paymodules_owner_bank_info = $ilance->admincp_paymodules->construct_paymodules_input('owner_bank_info', $ilpage['settings'] . '?cmd=paymodules');

($apihook = $ilance->api('admincp_template_payment_modules_end')) ? eval($apihook) : false;

// #### TAX MODULE #############################################################
$taxlabel = $countryname = $state = $city = $amount = $entirecountry_cb = '';

// #### UPDATE TAX ZONE ########################################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-taxzone')
{
	$updtax = $ilance->db->query("
		SELECT taxid, taxlabel, state, countryname, countryid, city, amount, invoicetypes, entirecountry
		FROM " . DB_PREFIX . "taxes
		WHERE taxid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($updtax) > 0)
	{
		$taxres = $ilance->db->fetch_array($updtax, DB_ASSOC);
		$taxlabel = $taxres['taxlabel'];
		$countryname = $taxres['countryname'];
		$state = $taxres['state'];
		$city = $taxres['city'];
		$amount = $taxres['amount'];
		$entirecountry = $taxres['entirecountry'];
		if ($taxres['entirecountry'])
		{
			$entirecountry_cb = 'checked="checked"';
			$headinclude .= "<script type=\"text/javascript\">
<!--
function disable_select() 
{
	document.ilform.state.disabled = true;
	document.ilform.city.disabled = true;
}
//-->
</script>
";
			$onload .= "return disable_select();";
		}
		if (!empty($taxres['invoicetypes']))
		{
			$checked1 = $checked2 = $checked3 = $checked4 = $checked5 = $checked6 = $checked7 = $checked8 = $checked9 = '';
			$invoicetypetax = unserialize($taxres['invoicetypes']);
			foreach ($invoicetypetax AS $invoicetype => $value)
			{
				switch ($invoicetype)
				{
					case 'storesubscription':
					{
						$checked1 .= 'checked="checked"';
						break;
					}							
					case 'subscription':
					{
						$checked2 .= 'checked="checked"';
						break;
					}							
					case 'commission':
					{
						$checked3 .= 'checked="checked"';
						break;
					}							
					case 'credential':
					{
						$checked4 .= 'checked="checked"';
						break;
					}							
					case 'portfolio':
					{
						$checked5 .= 'checked="checked"';
						break;
					}							
					case 'enhancements':
					{
						$checked6 .= 'checked="checked"';
						break;
					}
					case 'lanceads':
					{
						$checked7 .= 'checked="checked"';
						break;
					}
					case 'insertionfee':
					{
						$checked8 .= 'checked="checked"';
						break;
					}
					case 'finalvaluefee':
					{
						$checked9 .= 'checked="checked"';
						break;
					}
				}
			}
		}
		else
		{
			$invoicetypetax = '';
		}
	}

	$tax_subcmd = 'update-taxzone';
	$tax_id_hidden = '<input type="hidden" name="taxid" value="' . intval($ilance->GPC['id']) . '" />';
	$taxsubmit = '<input type="submit" value="{_update}" class="buttons" style="font-size:15px" />';
	$invoicetypetax = '';
	
	// tax types currently supported
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="subscription"><input type="checkbox" name="taxtype[subscription]" id="subscription" value="1" ' . $checked2 . ' /> {_member_subscription_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="commission"><input type="checkbox" name="taxtype[commission]" id="commission" value="1" ' . $checked3 . ' /> {_escrow_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="credential"><input type="checkbox" name="taxtype[credential]" id="credential" value="1" ' . $checked4 . ' /> {_credential_verification_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="portfolio"><input type="checkbox" name="taxtype[portfolio]" id="portfolio" value="1" ' . $checked5 . ' /> {_featured_portfolio_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="enhancements"><input type="checkbox" name="taxtype[enhancements]" id="enhancements" value="1" ' . $checked6 . ' /> {_auction_enhancement_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="insertionfee"><input type="checkbox" name="taxtype[insertionfee]" id="insertionfee" value="1" ' . $checked8 . ' /> {_insertion_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="finalvaluefee"><input type="checkbox" name="taxtype[finalvaluefee]" id="finalvaluefee" value="1" ' . $checked9 . ' /> {_final_value_fees}</label></div>';
	
	($apihook = $ilance->api('admincp_update_taxzone_end')) ? eval($apihook) : false;
}
else
{
	$tax_subcmd = 'create-taxzone';
	$taxsubmit = '<input type="submit" value="{_create}" class="buttons" style="font-size:15px" />';
	$tax_id_hidden = $invoicetypetax = '';			
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="subscription"><input type="checkbox" name="taxtype[subscription]" id="subscription" value="1" /> {_member_subscription_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="commission"><input type="checkbox" name="taxtype[commission]" id="commission" value="1" /> {_escrow} {_commission_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="credential"><input type="checkbox" name="taxtype[credential]" id="credential" value="1" /> {_credential_verification_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="portfolio"><input type="checkbox" name="taxtype[portfolio]" id="portfolio" value="1" /> {_featured_portfolio_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="enhancements"><input type="checkbox" name="taxtype[enhancements]" id="enhancements" value="1" /> {_auction_enhancement_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="insertionfee"><input type="checkbox" name="taxtype[insertionfee]" id="insertionfee" value="1" /> {_insertion_fees}</label></div>';
	$invoicetypetax .= '<div style="padding-bottom:4px"><label for="finalvaluefee"><input type="checkbox" name="taxtype[finalvaluefee]" id="finalvaluefee" value="1" /> {_final_value_fees}</label></div>';
	$dynamic_js_bodyend = '';
	
	($apihook = $ilance->api('admincp_create_taxzone_start')) ? eval($apihook) : false;
}
    
$show['no_taxes'] = true;
$sqltax = $ilance->db->query("
	SELECT taxid, taxlabel, state, countryname, countryid, city, amount, invoicetypes, entirecountry
	FROM " . DB_PREFIX . "taxes
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sqltax) > 0)
{
	$taxcount = 0;
	while ($tax = $ilance->db->fetch_array($sqltax, DB_ASSOC))
	{
		$tax['class'] = ($taxcount % 2) ? 'alt2' : 'alt1';
		$tax['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=paymodules&amp;subcmd=_remove-taxzone&amp;id=' . $tax['taxid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$tax['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=paymodules&amp;subcmd=_update-taxzone&amp;id=' . $tax['taxid'] . '#editzone"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		$tax['entire'] = ($tax['entirecountry']) ? '{_yes}' : '{_no}';
		if (!empty($tax['invoicetypes']))
		{
			$invoicetypetaxx = unserialize($tax['invoicetypes']);
			$typex = '';
			foreach ($invoicetypetaxx AS $invoicetypex => $value)
			{
				$typex .= ucfirst($invoicetypex) . ', ';
			}
			$typex = mb_substr($typex, 0, -2);
			$tax['types'] = $typex;
		}
		else
		{
			$tax['types'] = '{_no_invoice_types_defined}';
		}
		if (empty($tax['state']))
		{
			$tax['state'] = '-';
		}
		if (empty($tax['city']))
		{
			$tax['city'] = '-';
		}
		$taxes[] = $tax;
		$taxcount = $taxcount+1;
	}
	$show['no_taxes'] = false;
}

$jscountry = isset($countryname) ? $countryname : $ilconfig['registrationdisplay_defaultcountry'];
$jsstate = isset($state) ? $state : $ilconfig['registrationdisplay_defaultstate'];
$jscity = isset($city) ? $city : $ilconfig['registrationdisplay_defaultcity'];
$countryid = fetch_country_id($jscountry, $_SESSION['ilancedata']['user']['slng']);
$country_js_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $jscountry, 'country', false, 'state');
$state_js_pulldown = $ilance->common_location->construct_state_pulldown($countryid, $jsstate, 'state');
// #### PAYMENT TYPES ##########################################
$sql = $ilance->db->query("
	SELECT id, title
	FROM " . DB_PREFIX . "payment_methods
	ORDER BY sort ASC
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$row['title'] = '<div><input type="text" name="title[' . $row['id'] . ']" value="' . stripslashes(handle_input_keywords($row['title'])) . '" class="input" size="75%" /></div><div style="padding-top:6px"><div class="gray">{' . handle_input_keywords($row['title']) . '}</div></div>';
		$row['action'] = '<a href="' . $ilpage['settings'] . '?cmd=paymodules&amp;subcmd=remove-paytype&amp;id=' . $row['id'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$temp = $ilance->admincp_paymodules->count_offline_payment_types($row['id']);
		$row['count'] = number_format($temp['count']);
		$row['totalcount'] = number_format($temp['totalcount']);
		unset($temp);
		$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';				
		$paytypes[] = $row;
		$row_count++;
	}
	$show['no_paytypes_rows'] = false;
}
else
{
	$show['no_paytypes_rows'] = true;
}
// #### OFFLINE DEPOSIT METHODS ################################################
$sql = $ilance->db->query("
	SELECT id, name, number, swift, sort, visible, company_name, company_address, custom_notes, fee
	FROM " . DB_PREFIX . "deposit_offline_methods
	ORDER BY sort ASC
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$row['name'] = '<div>' . $row['name'] . '</div>';
		$row['company_name'] = '<div>' . $row['company_name'] . '</div>';
		$row['company_address'] = '<div>' . $row['company_address'] . '</div>';
		$row['number'] = '<div>' . $row['number'] . '</div>';
		$row['swift'] = '<div>' . $row['swift'] . '</div>';
		$row['custom_notes'] = '<div>' . $row['custom_notes'] . '</div>';
		$row['fee'] = '<div>' . $row['fee'] . '</div>';
		$row['sort'] = '<div>' . $row['sort'] . '</div>';
		$row['visible'] = '<div>' . (($row['visible'] == '1') ? '{_yes}' : '{_no}') . '</div>';
		$row['action'] = '<a href="' . $ilpage['settings'] . '?cmd=paymodules&amp;subcmd=edit-offline-deposit&amp;id=' . $row['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a><a href="' . $ilpage['settings'] . '?cmd=paymodules&amp;subcmd=remove-offline-deposit&amp;id=' . $row['id'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$row['class'] = ($row_count % 2) ? 'alt1' : 'alt2';			
		$offline_deposit[] = $row;
		$row_count++;
	}
	
	$show['no_offline_deposit_rows'] = false;
}
else
{
	$show['no_offline_deposit_rows'] = true;
}

if (isset($show['edit']) AND $show['edit'])
{
	$hidden = '<input type="hidden" name="subcmd" value="update-offline-deposit" />';
	$hidden .= '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
	$opt1 = ($visible == '1') ? 'checked="checked"' : '';
	$opt2 = ($visible == '1') ? '' : 'checked="checked"';
}
else 
{
	$name = $number = $swift = $companyname = $company_address = $custom_notes = $visible = $sort = '';
	$company_name = $ilconfig['globalserversettings_companyname'];
	$company_address = $ilconfig['globalserversettings_siteaddress'];
	$opt1 = 'checked="checked"';
	$opt2 = '';
	$hidden = '<input type="hidden" name="subcmd" value="insert-offline-deposit" />';
}

$pprint_array = array('paymodules_owner_bank_info','fee','opt1','opt2','name','number','swift','sort','hidden','company_address','companyname','custom_notes','paymodules_paypal_pro','paymodules_platnosci','paymodules_bluepay','entirecountry_cb','country_js_pulldown','state_js_pulldown','dynamic_js_bodyend','paymodules_moneybookers','paymodules_cashu','paymodules_psigate','paymodules_eway','paymodules_nochex','taxsubmit','tax_subcmd','tax_id_hidden','invoicetypetax','state','city','amount','taxlabel','countryname','configuration_invoicesystem','configuration_escrowsystem','paymodules_plugnpay','paymodules_keys','paymodules_bank','paymodules_check','paymodules_defaultipn','paymodules_paypal','paymodules_defaultgateway','paymodules_authnet','tctfee','tcwfee','pptfee','ppwfee','antfee','anactive','max_cc_verify_attempts','checked_enable_outside_fees_true','checked_enable_outside_fees_false','checked_enable_internal_fees_true','checked_enable_internal_fees_false','checked_wt_active_true','checked_wt_active_false','checked_multi_bankaccount_support','checked_disable_cc_on_processor_decline','checked_multi_creditcard_support','checked_refund_on_max_cc_attempts','checked_creditcard_authentication','checked_authnet_enabled_true','checked_authnet_enabled_false');

($apihook = $ilance->api('admincp_paymodules_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'paymodules.html', 1);
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'taxes');
$ilance->template->parse_loop('main', 'paytypes');
$ilance->template->parse_loop('main', 'offline_deposit');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>