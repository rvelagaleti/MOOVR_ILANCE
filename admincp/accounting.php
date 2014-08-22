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
		'tabfx'
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
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[accounting]" => $ilcrumbs["$ilpage[accounting]"]);
if(($v3nav = $ilance->cache->fetch("print_admincp_nav_accounting")) === false)
{
	$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['accounting']);
	$ilance->cache->store("print_admincp_nav_accounting", $v3nav);
}
if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
{
	refresh(HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
// #### ESCROW LISTINGS AND MANAGEMENT #########################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'escrow')
{
	include_once(DIR_ADMIN . 'accounting_escrow.php');
}
// #### WITHDRAW MANAGEMENT ####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'withdraws')
{
	include_once(DIR_ADMIN . 'accounting_withdraws.php');
}
// #### CREDIT CARD LISTINGS AND MANAGEMENT ####################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'creditcards')
{
	$area_title = '{_credit_card_management}';
	$page_title = SITE_NAME . ' - {_credit_card_management}';

	($apihook = $ilance->api('admincp_creditcard_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=creditcards', $_SESSION['ilancedata']['user']['slng']);
	
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	
	$orderlimit = ' ORDER BY cc_id DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$sqlverified = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE authorized = 'yes' " . $orderlimit;
	$sqlverified2 = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE authorized = 'yes'";
	$resultverified = $ilance->db->query($sqlverified);        
	if ($ilance->db->num_rows($resultverified) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($resultverified))
		{
			$res['ccnum'] = substr_replace($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), 'XX XXXX XXXX ', 2 , (mb_strlen($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) - 6));
			$res['username'] = stripslashes($res['name_on_card']);
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
			$res['phone'] = $res['phone_of_cardowner'];
			$res['expiry'] = $res['creditcard_expiry'];
			
			if ($res['authorized'] == 'yes')
			{
			    $res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_unauthorize_credit_card_cannot_use_card}" border="0" /></a>';
			}
			else
			{
			    $res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_authorize_credit_card_can_use_card}" border="0" /></a>';
			}
			
			$res['address'] = $res['card_billing_address1'].", ";
			if ($res['card_billing_address2'] != "")
			{
			    $res['address'] .= $res['card_billing_address2'].", ";
			}
			$res['address'] .= ucfirst($res['card_city']).", ".ucfirst($res['card_state']).", ".mb_strtoupper($res['card_postalzip']).", ";
			$res['address'] .= stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$res['cc_id'],"card_country"),"location_eng"));
					
			if ($res['creditcard_type'] == 'visa')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'mc')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'amex')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'disc')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="">';
			}
			$res['status'] = ucfirst($res['creditcard_status']);
			$res['remove'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
			$res['authamounts'] = $ilance->currency->format($res['auth_amount1'] + $res['auth_amount2']);
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$verifiedcreditcards[] = $res;
			$row_count++;
		}
	}
	else
	{
		$show['no_verifiedcreditcards'] = true;
	}		
	$resultverified2 = $ilance->db->query($sqlverified2);
	if ($ilance->db->num_rows($resultverified2) > 0)
	{
		$numberverified = $ilance->db->num_rows($resultverified2);
	}
	else
	{
		$numberverified = 0;
	}		
	$verifiedprevnext = print_pagnation($numberverified, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], ($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=creditcards&amp;viewtype=verified");
	
	// #### UNVERIFIED CREDIT CARDS ON FILE ################################
	if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
	{
		$ilance->GPC['page2'] = 1;
	}
	else
	{
		$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
	}
	
	$orderlimit2 = ' ORDER BY cc_id DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];    
	$sqlunverified = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE authorized = 'no' " . $orderlimit2;
	$sqlunverified2 = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE authorized = 'no'";        
	$resultunverified = $ilance->db->query($sqlunverified);
	if ($ilance->db->num_rows($resultunverified) > 0)
	{
		$row_count2 = 0;
		while ($res = $ilance->db->fetch_array($resultunverified))
		{
			$res['ccnum'] = substr_replace($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), 'XX XXXX XXXX ', 2 , (mb_strlen($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) - 6));
			$res['username'] = stripslashes($res['name_on_card']);
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
			$res['phone'] = $res['phone_of_cardowner'];
			$res['expiry'] = $res['creditcard_expiry'];
			if ($res['authorized'] == "yes")
			{
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_unauthorize_credit_card_cannot_use_card}" border="0" /></a>';
			}
			else
			{
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_authorize_credit_card_can_use_card}" border="0" /></a>';
			}				
			$res['address'] = $res['card_billing_address1'].", ";				
			if ($res['card_billing_address2'] != "")
			{
			    $res['address'] .= $res['card_billing_address2'].", ";
			}				
			$res['address'] .= ucfirst($res['card_city']).", ".ucfirst($res['card_state']).", ".mb_strtoupper($res['card_postalzip']).", ";
			$res['address'] .= stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$res['cc_id'],"card_country"),"location_eng"));
			
			if ($res['creditcard_type'] == 'visa')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'mc')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'amex')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'disc')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="">';
			}				
			$res['status'] = ucfirst($res['creditcard_status']);
			$res['remove'] = '<a href="'.$ilpage['subscribers'] . '?subcmd=_remove-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
			$res['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			$unverifiedcreditcards[] = $res;
			$row_count2++;
		}
	}
	else
	{
		$show['no_unverifiedcreditcards'] = true;
	}	    
	$resultunverified2 = $ilance->db->query($sqlunverified2);		
	if ($ilance->db->num_rows($resultunverified2) > 0)
	{
		$numberunverified = $ilance->db->num_rows($resultunverified2);
	}
	else
	{
		$numberunverified = 0;
	}		
	$unverifiedprevnext = print_pagnation($numberunverified, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], ($ilance->GPC['page2']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=creditcards&amp;viewtype=unverified", 'page2');

	// #### EXPIRED CREDIT CARDS ON FILE ###################################
	if (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0)
	{
		$ilance->GPC['page3'] = 1;
	}
	else
	{
		$ilance->GPC['page3'] = intval($ilance->GPC['page3']);
	}
	
	$orderlimit3 = ' ORDER BY cc_id DESC LIMIT ' . (($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$sqlexpired  = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE creditcard_status = 'expired' " . $orderlimit3;
	$sqlexpired2 = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE creditcard_status = 'expired'";        
	$resultexpired = $ilance->db->query($sqlexpired);        
	if ($ilance->db->num_rows($resultexpired) > 0)
	{
		$row_count3 = 0;
		while ($res = $ilance->db->fetch_array($resultexpired))
		{
			$res['ccnum'] = substr_replace($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), 'XX XXXX XXXX ', 2 , (mb_strlen($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) - 6));
			$res['username'] = stripslashes($res['name_on_card']);
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">'.fetch_user('username', $res['user_id']).'</a>';
			$res['phone'] = $res['phone_of_cardowner'];
			$res['expiry'] = $res['creditcard_expiry'];
			
			if ($res['authorized'] == 'yes')
			{
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to unauthorize credit card (cannot use card)" border="0"></a>';
			}
			else
			{
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to authorize credit card (can use card)" border="0"></a>';
			}
			
			$res['address'] = $res['card_billing_address1'].", ";                
			if ($res['card_billing_address2'] != "")
			{
				$res['address'] .= $res['card_billing_address2'].", ";
			}				
			$res['address'] .= ucfirst($res['card_city']).", ".ucfirst($res['card_state']).", ".mb_strtoupper($res['card_postalzip']).", ";
			$res['address'] .= stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$res['cc_id'],"card_country"),"location_eng"));
			if ($res['creditcard_type'] == 'visa')
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'mc')
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'amex')
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'disc')
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="">';
			}
			$res['status'] = ucfirst($res['creditcard_status']);
			$res['remove'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
			$res['class'] = ($row_count3 % 2) ? 'alt2' : 'alt1';
			$expiredcreditcards[] = $res;
			$row_count3++;
		}
	}
	else
	{
		$show['no_expiredcreditcards'] = true;
	}		
	$resultexpired2 = $ilance->db->query($sqlexpired2);
	if ($ilance->db->num_rows($resultexpired2) > 0)
	{
		$numberexpired = $ilance->db->num_rows($resultexpired2);
	}
	else
	{
		$numberexpired = 0;
	}
	
	$expiredprevnext = print_pagnation($numberexpired, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page3'], ($ilance->GPC['page3']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=creditcards&amp;viewtype=expired", 'page3');
	
	$pprint_array = array('numberexpired','expiredprevnext','unverifiedprevnext','numberunverified','verifiedprevnext','numberverified','id');
	
	($apihook = $ilance->api('admincp_accounting_creditcards_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'creditcards.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','verifiedcreditcards','unverifiedcreditcards','expiredcreditcards'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### BANK ACCOUNTS ##########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bankaccounts')
{
	$area_title = '{_bank_account_management}';
	$page_title = SITE_NAME . ' - {_bank_account_management}';

	($apihook = $ilance->api('admincp_bankaccount_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=bankaccounts', $_SESSION['ilancedata']['user']['slng']);
	
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	
	$orderlimit = ' ORDER BY bank_id DESC LIMIT '.(($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay']).','.$ilconfig['globalfilters_maxrowsdisplay'];
	$sqlbankaccounts  = "SELECT * FROM " . DB_PREFIX . "bankaccounts ".$orderlimit;
	$sqlbankaccounts2 = "SELECT * FROM " . DB_PREFIX . "bankaccounts";
	$result = $ilance->db->query($sqlbankaccounts);
	if ($ilance->db->num_rows($result) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($result))
		{
			$res['bankname'] = ucfirst(stripslashes($res['beneficiary_bank_name']));
			$res['accountnum'] = $res['beneficiary_account_number'];
			$res['accounttype'] = ucfirst($res['bank_account_type']);
			$res['address'] = ucfirst(stripslashes($res['beneficiary_bank_address_1']));
			$res['swiftnum'] = $res['beneficiary_bank_routing_number_swift'];
			if ($res['beneficiary_bank_address_2'] != "")
			{
			    $res['address'] .= ", ".stripslashes($res['beneficiary_bank_address_2']); 
			}				
			$res['city'] = ucfirst($res['beneficiary_bank_city']);
			$res['zipcode'] = mb_strtoupper($res['beneficiary_bank_zipcode']);
			$res['country'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "bankaccounts","bank_id=".$res['bank_id'],"beneficiary_bank_country_id"),"location_eng"));
			$res['currency'] = $ilance->db->fetch_field(DB_PREFIX . "currency","currency_id=".$ilance->db->fetch_field(DB_PREFIX . "bankaccounts","bank_id=".$res['bank_id'],"destination_currency_id"),"currency_abbrev");
			$res['username'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id='.$res['user_id'].'">'.fetch_user('username', $res['user_id']).'</a>';
			$res['remove'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-bankaccount&amp;id='.$res['bank_id'].'&amp;uid='.$res['user_id'].'" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$bankaccounts[] = $res;
			$row_count++;
		}
	}
	else
	{
		$show['no_bankaccounts'] = true;
	}
		
	$numberactive = $ilance->db->num_rows($ilance->db->query($sqlbankaccounts2));
	$activeprevnext = print_pagnation($numberactive, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], ($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=bankaccounts&amp;viewtype=active");
	
	$pprint_array = array('activeprevnext','numberactive','id');
	
	($apihook = $ilance->api('admincp_accounting_bankaccounts_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'bankaccounts.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','bankaccounts'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### CUSTOM REPORT MANAGEMENT ###############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'reports')
{
	include_once(DIR_ADMIN . 'accounting_reports.php');
}
// #### CURRENCY MANAGEMENT ####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'currencies')
{
	// #### REMOVE CURRENCY HANDLER ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-currency' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		if ($ilconfig['globalserverlocale_defaultcurrency'] != $ilance->GPC['id'])
		{
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "currency
				WHERE currency_id = '" . intval($ilance->GPC['id']) . "'
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET currencyid = '" . $ilconfig['globalserverlocale_defaultcurrency'] . "'
				WHERE currencyid = '" . intval($ilance->GPC['id']) . "'
			");
			$ilance->cachecore->delete("currencies");
			print_action_success('{_the_selected_currency_rate_was_deleted_successfully}', $ilpage['accounting'] . '?cmd=currencies');
			exit();
		}
		else
		{
			print_action_failed('{_you_cannot_delete_this_currency_because_it_appears_it_is_associated_as_the_main_marketplace_currency}', $ilpage['accounting'] . '?cmd=currencies');
			exit();        
		}
	}
	// #### UPDATE CURRENCIES HANDLER ##############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-currency')
	{
		$query = '';
		foreach ($ilance->GPC['currency'] AS $currencyid)
		{
			foreach ($currencyid AS $key => $value)
			{
				if ($key == 'currency_id')
				{
					$query .= " time = '" . DATETIME24H . "' WHERE currency_id = '" . intval($value) . "'";
				}
				else
				{
					$query .= "$key = '" . $ilance->db->escape_string($value) . "', ";
				}
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "currency
				SET " . $query . "
			");
			$query = '';
		}
		$ilance->cachecore->delete("currencies");
		print_action_success('{_currency_rates_were_updated_successfully_changes_should_take_effect_immediately}', $ilpage['accounting'] . '?cmd=currencies');
		exit();
	}
	// #### CREATE NEW CURRENCY HANDLER ####################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-currency')
	{
		if (!empty($ilance->GPC['currency_name']) AND !empty($ilance->GPC['rate']) AND !empty($ilance->GPC['currency_abbrev']) AND !empty($ilance->GPC['symbol_left']))
		{
			$sql = $ilance->db->query("
				INSERT INTO " . DB_PREFIX . "currency
				(currency_id, currency_abbrev, currency_name, rate, time, isdefault, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($ilance->GPC['currency_abbrev']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['currency_name']) . "',
				'" . $ilance->db->escape_string(floatval($ilance->GPC['rate'])) . "',
				'" . DATETIME24H . "',
				'0',
				'" . $ilance->db->escape_string($ilance->GPC['symbol_left']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['symbol_right']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['decimal_point']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['thousands_point']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['decimal_places']) . "')
			");
			$ilance->cachecore->delete("currencies");
			print_action_success('{_the_new_currency_rate_was_successfully_created_within_the_database}', $ilpage['accounting'] . '?cmd=currencies');
			exit();
		}
		else 
		{
			print_action_success('{_please_enter_all_fields}', $ilpage['accounting'] . '?cmd=currencies');
			exit();
		}
	}
	else
	{
		$area_title = '{_currency_management}';
		$page_title = SITE_NAME . ' - {_currency_management}';
    
		($apihook = $ilance->api('admincp_currency_settings')) ? eval($apihook) : false;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=currencies', $_SESSION['ilancedata']['user']['slng']);
		$result = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "currency
			WHERE isdefault = '1'
		");
		if ($ilance->db->num_rows($result) > 0)
		{
			$row_count = 0;                            
			while ($res = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				$res['currencyname'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_name]" value="' . stripslashes($res['currency_name']) . '" size="30" class="input" />';
				$res['rate'] = '<input type="text" name="currency[' . $res['currency_id'] . '][rate]" value="' . stripslashes($res['rate']) . '" size="7" class="input" />';
				$res['abbrev'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_abbrev]" value="' . stripslashes($res['currency_abbrev']) . '" size="4" class="input" />';
				$res['symbolleft'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_left]" value="' . stripslashes($res['symbol_left']) . '" size="4" class="input" />';
				$res['symbolright'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_right]" value="' . stripslashes($res['symbol_right']) . '" size="4" class="input" />';
				$res['decimalpoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_point]" value="' . stripslashes($res['decimal_point']) . '" size="2" class="input" />';
				$res['thousandspoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][thousands_point]" value="' . stripslashes($res['thousands_point']) . '" size="2" class="input" />';
				$res['decimalplaces'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_places]" value="' . stripslashes($res['decimal_places']) . '" size="2" class="input" />';
				$sql = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE currencyid = '" . $res['currency_id'] . "'
				");
				$num = $ilance->db->num_rows($sql);
				$delete = ($num == 0) ? '<a href="'.$ilpage['accounting'] . '?cmd=currencies&amp;subcmd=_remove-currency&amp;id=' . $res['currency_id'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>' : '<span title="Cannot remove currency (currently in use)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" alt="" border="0" /></span>';
				$res['actions'] = '<input type="hidden" name="currency[' . $res['currency_id'] . '][currency_id]" value="' . $res['currency_id'] . '" /> ' . $delete;
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1'; 
				$defaultcurrencies[] = $res;
				$row_count++;
			}
		}
		else
		{
			$show['no_defaultcurrencies'] = true;
		}
		$resultcustom = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "currency
			WHERE isdefault != '1'
		");
		if ($ilance->db->num_rows($resultcustom) > 0)
		{
			$row_count = 0;                            
			while ($res = $ilance->db->fetch_array($resultcustom))
			{
				$res['currencyname'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_name]" value="' . stripslashes($res['currency_name']) . '" size="30" class="input" />';
				$res['rate'] = '<input type="text" name="currency[' . $res['currency_id'] . '][rate]" value="' . stripslashes($res['rate']) . '" size="7" class="input" />';
				$res['abbrev'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_abbrev]" value="' . stripslashes($res['currency_abbrev']) . '" size="4" class="input" />';
				$res['symbolleft'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_left]" value="' . stripslashes($res['symbol_left']) . '" size="4" class="input" />';
				$res['symbolright'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_right]" value="' . stripslashes($res['symbol_right']) . '" size="4" class="input" />';
				$res['decimalpoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_point]" value="' . stripslashes($res['decimal_point']) . '" size="2" class="input" />';
				$res['thousandspoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][thousands_point]" value="' . stripslashes($res['thousands_point']) . '" size="2" class="input" />';
				$res['decimalplaces'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_places]" value="' . stripslashes($res['decimal_places']) . '" size="2" class="input" />';
				$sql = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE currencyid = '" . $res['currency_id'] . "'
				");
				$num = $ilance->db->num_rows($sql);
				$delete = ($num == 0) ? '<a href="'.$ilpage['accounting'] . '?cmd=currencies&amp;subcmd=_remove-currency&amp;id=' . $res['currency_id'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>' : '<span title="Cannot remove currency (currently in use)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" alt="" border="0" /></span>';
				$res['actions'] = '<input type="hidden" name="currency[' . $res['currency_id'] . '][currency_id]" value="' . $res['currency_id'] . '" /> ' . $delete;
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$customcurrencies[] = $res;
				$row_count++;
			}
		}
		else
		{
			$show['no_customcurrencies'] = true;
		}
		$currencyname = '<input type="text" name="currency_name" value="' . stripslashes($res['currency_name']) . '" size="30" class="input">';
		$rate = '<input type="text" name="rate" value="' . stripslashes($res['rate']) . '" size="7" class="input" />';
		$abbrev = '<input type="text" name="currency_abbrev" value="' . stripslashes($res['currency_abbrev']) . '" size="4" class="input" />';
		$symbolleft = '<input type="text" name="symbol_left" value="' . stripslashes($res['symbol_left']) . '" size="4" class="input" />';
		$symbolright = '<input type="text" name="symbol_right" value="' . stripslashes($res['symbol_right']) . '" size="4" class="input" />';
		$decimalpoint = '<input type="text" name="decimal_point" value="' . stripslashes($res['decimal_point']) . '" size="2" class="input" />';
		$thousandspoint = '<input type="text" name="thousands_point" value="' . stripslashes($res['thousands_point']) . '" size="2" class="input" />';
		$decimalplaces = '<input type="text" name="decimal_places" value="' . stripslashes($res['decimal_places']) . '" size="2" class="input" />';
		$global_currencysettings = $ilance->admincp->construct_admin_input('globalserverlocalecurrency', $ilpage['accounting'] . '?cmd=currencies');
		$pprint_array = array('global_currencysettings','symbolleft','symbolright','decimalpoint','thousandspoint','decimalplaces','currencyname','rate','abbrev','symbol','id');
		
		($apihook = $ilance->api('admincp_accounting_currencies_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'currencies.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','defaultcurrencies','customcurrencies'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
// #### REMOVING A TRANSACTION #################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-invoice' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "attachment
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "buynow_orders
		SET invoiceid = '0',
		escrowfeeinvoiceid = '0',
		escrowfeebuyerinvoiceid = '0',
		fvfinvoiceid = '0',
		fvfbuyerinvoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "portfolio
		SET featured_invoiceid = '0'
		WHERE featured_invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "profile_answers
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET insertionfee = '0',
		ifinvoiceid = '0',
		isifpaid = '0'
		WHERE ifinvoiceid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET fvf = '0',
		fvfinvoiceid = '0',
		isfvfpaid = '0'
		WHERE fvfinvoiceid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects_escrow
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	// this is a final value fee.. update auction listing table
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects_escrow
		SET isfeepaid = '0',
		feeinvoiceid = '0'
		WHERE feeinvoiceid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects_escrow
		SET isfee2paid = '0',
		fee2invoiceid = '0'
		WHERE fee2invoiceid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "referral_data
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "subscription_user
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "subscription_user_exempt
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	($apihook = $ilance->api('admincp_remove_invoice_end')) ? eval($apihook) : false;
	
	print_action_success('{_the_selected_invoice_was_removed_from_the_system}', $ilpage['accounting']);
	exit();
}
// #### MARKING A CHARITY AS BEING PAID ########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-charity-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET ischaritypaid = '1'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
			AND isdonationfee = '1'
	");
	
	$charityid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "charityid");
	if ($charityid > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "charities
			SET donations = donations + 1,
			earnings = earnings + " . $ilance->db->escape_string($ilance->GPC['amount']) . "
			WHERE charityid = '" . intval($charityid) . "'
				LIMIT 1
		");
	}
	
	($apihook = $ilance->api('admincp_mark_charity_invoice_paid_end')) ? eval($apihook) : false;
	
	print_action_success('{_the_amount_owing_from_this_users_payment_has_been_marked}', $ilpage['accounting']);
	exit();
}
// #### MARKING A TRANSACTION AS PAID ##########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-credit-invoice-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['amount_' . $ilance->GPC['id']]))
{
		$amount = sprintf("%01.2f", $ilance->GPC['amount_' . intval($ilance->GPC['id'])]);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET status = 'paid',
			amount = '" . $amount . "',
			paid = '" . $amount . "',
			totalamount = '" . $amount . "',
			paiddate = '" . DATETIME24H . "'
			WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
		");
		
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res_invoice = $ilance->db->fetch_array($sql);
		
			$sql = $ilance->db->query("
				SELECT available_balance, total_balance
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($res_invoice['user_id']) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql);
				$new_credit_amount = $amount;
				
				$total_now = $res['total_balance'];
				$avail_now = $res['available_balance'];
				
				$new_total_now = ($total_now + $new_credit_amount);
				$new_avail_now = ($avail_now + $new_credit_amount);
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET total_balance = '" . $new_total_now . "',
					available_balance = '" . $new_avail_now . "'
					WHERE user_id = '" . intval($res_invoice['user_id']) . "'");
				
				//$ilance->accounting_payment->insert_income_reported(intval($res_invoice['user_id']), sprintf("%01.2f", $amount), 'credit');
				
				$sqlemail = $ilance->db->query("
					SELECT email, username, first_name, last_name
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($res_invoice['user_id']) . "'
				");
				if ($ilance->db->num_rows($sqlemail) > 0)
				{
					$resemail = $ilance->db->fetch_array($sqlemail);
					
					
					$ilance->email->mail = $resemail['email'];
					$ilance->email->slng = fetch_user_slng(intval($res_invoice['user_id']));
					$ilance->email->get('account_credit_notification');		
					$ilance->email->set(array(
						'{{customer}}' => $resemail['username'],
						'{{amount}}' => $ilance->currency->format($amount),
						'{{from}}' => $_SESSION['ilancedata']['user']['username']
					));
					$ilance->email->send();
					
					print_action_success('{_the_selected_invoice_was_marked_as_being_paid_in_full}', $ilpage['accounting']);
					exit();					
				}
			}
		}
}
// #### MARKING A TRANSACTION AS PAID ##########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-invoice-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'paid',
		    paid = totalamount,
		    paiddate = '" . DATETIME24H . "'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	
	$sqli = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sqli) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sqli, DB_ASSOC);
		
		if ($res_invoice['isif'] == '1')
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isifpaid = '1'
				WHERE ifinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
			if (fetch_auction('status', $res_invoice['projectid']) == 'frozen' AND ((fetch_auction('isenhancementfeepaid', $res_invoice['projectid']) == '1' AND fetch_auction('enhancementfeeinvoiceid', $res_invoice['projectid']) != '0') OR (fetch_auction('isenhancementfeepaid', $res_invoice['projectid']) == '0' AND fetch_auction('enhancementfeeinvoiceid', $res_invoice['projectid']) == '0') ))
			{
				$sql_date = '';
				$sql = $ilance->db->query("
					SELECT date_starts, date_end, featured_date, user_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $res_invoice['projectid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				if (strtotime($res['date_starts']) < strtotime(DATETIME24H))
				{
					$date_starts = strtotime($res['date_starts']);
					$date_end = strtotime($res['date_end']);
					$auction_time = $date_end - $date_starts;

					$date_starts = DATETIME24H;
					$date_end = date("Y-m-d H:i:s", strtotime(DATETIME24H) + $auction_time);
					$sql_date = " ,date_starts = '" . $date_starts ."', date_end = '" . $date_end ."'";
					$sql_date .= ($res['featured_date'] != '0000-00-00 00:00:00') ? " ,featured_date = '" . DATETIME24H . "'" : "";
				}
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = 'open'
					    $sql_date
					WHERE project_id = '" . intval($res_invoice['projectid']) . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->referral->update_referral_action('postauction', $res['user_id']);
				$cid = fetch_auction('cid', intval($res_invoice['projectid']));
				$state = fetch_auction('project_state', intval($res_invoice['projectid']));
				$ilance->categories->build_category_count($cid, 'add', "insert_" . $state . "_auction(): adding increment count category id $cid");
			}
		}
		else if ($res_invoice['isenhancementfee'] == '1')
		{
			// this is an enhancements fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isenhancementfeepaid = '1'
				WHERE enhancementfeeinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
			if (fetch_auction('status', $res_invoice['projectid']) == 'frozen' AND ((fetch_auction('isifpaid', $res_invoice['projectid']) == '1' AND fetch_auction('ifinvoiceid', $res_invoice['projectid']) != '0') OR (fetch_auction('isifpaid', $res_invoice['projectid']) == '0' AND fetch_auction('ifinvoiceid', $res_invoice['projectid']) == '0') ))
			{
				$sql_date = '';
				$sql = $ilance->db->query("
					SELECT date_starts, date_end, featured_date, user_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $res_invoice['projectid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				if (strtotime($res['date_starts']) < strtotime(DATETIME24H))
				{
					$date_starts = strtotime($res['date_starts']);
					$date_end = strtotime($res['date_end']);
					$auction_time = $date_end - $date_starts;

					$date_starts = DATETIME24H;
					$date_end = date("Y-m-d H:i:s", strtotime(DATETIME24H) + $auction_time);
					$sql_date = " ,date_starts = '" . $date_starts ."', date_end = '" . $date_end ."'";
					$sql_date .= ($res['featured_date'] != '0000-00-00 00:00:00') ? " ,featured_date = '" . DATETIME24H . "'" : "";
				}
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = 'open'
					$sql_date
					WHERE project_id = '" . intval($res_invoice['projectid']) . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->referral->update_referral_action('postauction', $res['user_id']);
				$cid = fetch_auction('cid', intval($res_invoice['projectid']));
				$state = fetch_auction('project_state', intval($res_invoice['projectid']));
				$ilance->categories->build_category_count($cid, 'add', "insert_" . $state . "_auction(): adding increment count category id $cid");
			}
		} 
		else if ($res_invoice['isfvf'] == '1')
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isfvfpaid = '1'
				WHERE fvfinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		else if ($res_invoice['isescrowfee'] == '1')
		{
			// this is a final value fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfeepaid = '1'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND feeinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfee2paid = '1'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND fee2invoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		
		($apihook = $ilance->api('admincp_mark_invoice_paid_end')) ? eval($apihook) : false;
		
		unset($res_invoice);
	}
	
	$sql = $ilance->db->query("
		SELECT invoiceid, projectid, buynowid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		
		// this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
		// let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
		// this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
		// at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
		$buynowcheck = $ilance->db->query("
			SELECT escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . $res_invoice['projectid'] . "'
				AND orderid = '" . $res_invoice['buynowid'] . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($buynowcheck) > 0)
		{
			$resbuynow = $ilance->db->fetch_array($buynowcheck);
			
			// #### handle seller escrow fee ###############
			if ($res_invoice['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeepaid = '1'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			
			// #### handle buyer escrow fee ################
			else if ($res_invoice['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeebuyerpaid = '1'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			
			// #### handle seller fvf's for items sold via buy now
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfpaid = '1'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			
			// #### handle buyer fvf for items bought (not used at the moment as sellers are only charged fvf's)..
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfbuyerpaid = '1'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
	}
	
	// #### handle subscription activation logic if we can #########
	$sql = $ilance->db->query("
		SELECT user_id, invoicetype, subscriptionid, paymethod
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($res_invoice['invoicetype'] == 'subscription' AND $res_invoice['subscriptionid'] > 0)
		{
			// #### activate currently selected subscription for user as it may be inactive due to payment and admin is marking invoice as paid..
			
			
			$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res_invoice['subscriptionid'] . "'", "units");
			$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res_invoice['subscriptionid'] . "'", "length");
			$roleid = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res_invoice['subscriptionid'] . "'", "roleid");
			$cost = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res_invoice['subscriptionid'] . "'", "cost");
			$startdate = DATETIME24H;
			$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($units, $length));
			
			// #### activate subscription plan for user ####
			$ilance->subscription_plan->activate_subscription_plan($res_invoice['user_id'], $startdate, $renewdate, 0, intval($ilance->GPC['id']), $res_invoice['subscriptionid'], $res_invoice['paymethod'], $roleid, $cost);
			
			// #### referral tracker for this user #########
			$ilance->referral->update_referral_action('subscription', $res_invoice['user_id']);
		}
		unset($res_invoice);
	}
	
	// #### handle donation nonprofit transactions
	$sql = $ilance->db->query("
		SELECT amount, isdonationfee, projectid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($res_invoice['isdonationfee'])
		{
			$sql2 = $ilance->db->query("
				SELECT charityid
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $res_invoice['projectid'] . "'
			");
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$resproject = $ilance->db->fetch_array($sql2);
				
				if ($resproject['charityid'] > 0)
				{
					// this is a final value donation fee.. update auction listing table
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET donermarkedaspaid = '1',
						donermarkedaspaiddate = '" . DATETIME24H . "'
						WHERE project_id = '" . $res_invoice['projectid'] . "'
					", 0, null, __FILE__, __LINE__);
					
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "charities
						SET donations = donations + 1,
						earnings = earnings + $res_invoice[amount]
						WHERE charityid = '" . $resproject['charityid'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
			}
		}
	}
	
	print_action_success('{_the_selected_invoice_was_marked_as_being_paid_in_full}', $ilpage['accounting']);
	exit();
}
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_send_reminder' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
     $ilance->accounting_reminders->send_unpaid_reminders(intval($ilance->GPC['id']));		
}
// #### MARKING A TRANSACTION AS UNPAID ########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-invoice-unpaid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'unpaid',
		paid = '0.00',
		paiddate = '0000-00-00 00:00:00'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		if ($res_invoice['isif'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isifpaid = '0'
				WHERE ifinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isfvf'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isfvfpaid = '0'
				WHERE fvfinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isescrowfee'])
		{
			// this is a final value fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfeepaid = '1'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND feeinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfee2paid = '1'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND fee2invoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		
		($apihook = $ilance->api('admincp_mark_invoice_unpaid_end')) ? eval($apihook) : false;
		
		unset($res_invoice);
	}
	
	$sql = $ilance->db->query("
		SELECT invoiceid, projectid, buynowid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		
		// this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
		// let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
		// this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
		// at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
		$buynowcheck = $ilance->db->query("
			SELECT escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . $res_invoice['projectid'] . "'
				AND orderid = '" . $res_invoice['buynowid'] . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($buynowcheck) > 0)
		{
			$resbuynow = $ilance->db->fetch_array($buynowcheck);
			if ($res_invoice['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeepaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeebuyerpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfbuyerpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
	}
	
	// #### handle donation nonprofit transactions
	$sql = $ilance->db->query("
		SELECT amount, isdonationfee, projectid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		if ($res_invoice['isdonationfee'])
		{
			$sql2 = $ilance->db->query("
				SELECT charityid
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $res_invoice['projectid'] . "'
			");
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$resproject = $ilance->db->fetch_array($sql2);
				
				if ($resproject['charityid'] > 0)
				{
					// this is a final value donation fee.. update auction listing table
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET donermarkedaspaid = '0',
						donermarkedaspaiddate = '0000-00-00 00:00:00'
						WHERE project_id = '" . $res_invoice['projectid'] . "'
					", 0, null, __FILE__, __LINE__);
					
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "charities
						SET donations = donations - 1,
						earnings = earnings - $res_invoice[amount]
						WHERE charityid = '" . $resproject['charityid'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
			}
		}
	}
	
	print_action_success('{_the_selected_invoice_was_marked_as_being_unpaid}', $ilpage['accounting']);
	exit();
}
// #### INVOICE REFUND #########################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_refund_invoice' AND isset($ilance->GPC['txn']) AND !empty($ilance->GPC['txn']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$sql = $ilance->db->query("
		SELECT user_id, custommessage
		FROM " . DB_PREFIX . "invoices
		WHERE transactionid = '" . $ilance->db->escape_string($ilance->GPC['txn']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		$transactionid = urlencode($res['custommessage']);
		$currencyid = urlencode($ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code']);	
		
		// Add request-specific fields to the request string.
		$nvpStr = "&TRANSACTIONID=$transactionid&REFUNDTYPE=Full&CURRENCYCODE=$currencyid";

		// Execute the API operation; see the PPHttpPost function above.
		$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
		$httpParsedResponseAr = $ilance->paypal->print_refund_payment_form($nvpStr);

		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
		{
			// record debit and remove funds from users online account balances
			$accountbal = $ilance->db->query("
				SELECT total_balance, available_balance
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $res['user_id'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($accountbal) > 0)
			{
				$sel_account_result = $ilance->db->fetch_array($accountbal, DB_ASSOC);

				$new_credit_total_balance = ($sel_account_result['total_balance'] - $chargeback['amount']);
				$new_credit_avail_balance = ($sel_account_result['available_balance'] - $chargeback['amount']);

				// construct new chargeback debit transaction
				
				$deposit_invoice_id = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					intval($res['user_id']),
					0,
					0,
					0,
					'Paypal [' . $ilance->paypal->get_payment_status() . '] Trigger Received [TXN_ID: ' . $ilance->paypal->get_transaction_id() . ']',
					sprintf("%01.2f", $chargeback['amount']),
					sprintf("%01.2f", $chargeback['amount']),
					'paid',
					'debit',
					'account',
					DATETIME24H,
					DATEINVOICEDUE,
					DATETIME24H,
					$ilance->paypal->get_transaction_id(),
					0,
					0,
					1,
					'',
					1,
					0
				);

				// debit members account
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
					total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
					WHERE user_id = '" . intval($res['user_id']) . "'
				", 0, null, __FILE__, __LINE__);

				print_action_success('{_the_selected_invoice_was_refunded}', $ilpage['accounting']);
				exit();
			}
		} 
		else  
		{	
			print_action_failed('<b>' . urldecode($httpParsedResponseAr['L_SEVERITYCODE0']) . " " . urldecode($httpParsedResponseAr['L_ERRORCODE0']) . " " . urldecode($httpParsedResponseAr['L_SHORTMESSAGE0']) . ". " . urldecode($httpParsedResponseAr['L_LONGMESSAGE0']) . '<br />{_paypal} {_transaction_id}: ' . $transactionid . '</b><br />', $ilpage['accounting']);
			exit();
		}
	}
	
	print_action_failed('{_invoice_error}', $ilpage['accounting']);
	exit();
}
// #### INVOICE CANCELLATION ###################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_cancel-invoice' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'cancelled'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		if ($res_invoice['isif'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isifpaid = '0'
				WHERE ifinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isfvf'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isfvfpaid = '0'
				WHERE fvfinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isescrowfee'])
		{
			// this is a final value fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfeepaid = '0'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND feeinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfee2paid = '0'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND fee2invoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		
		($apihook = $ilance->api('admincp_mark_invoice_cancelled_end')) ? eval($apihook) : false;
		
		unset($res_invoice);
	}
	
	$sql = $ilance->db->query("
		SELECT invoiceid, projectid, buynowid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		
		// this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
		// let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
		// this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
		// at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
		$buynowcheck = $ilance->db->query("
			SELECT escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . $res_invoice['projectid'] . "'
				AND orderid = '" . $res_invoice['buynowid'] . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($buynowcheck) > 0)
		{
			$resbuynow = $ilance->db->fetch_array($buynowcheck);
			if ($res_invoice['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeepaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeebuyerpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfbuyerpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
	}
	
	print_action_success('{_the_selected_invoice_was_cancelled}', $ilpage['accounting']);
	exit();
}
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoice' AND isset($ilance->GPC['view']) AND !empty($ilance->GPC['view']))
{
	$area_title = '{_invoice_management}';
	$page_title = SITE_NAME . ' - {_invoice_management}';
	exit;
}
// #### MAIN INVOICE PAGE ######################################################
else
{
	$area_title = '{_invoice_management}';
	$page_title = SITE_NAME . ' - {_invoice_management}';
	
	($apihook = $ilance->api('admincp_accounting_settings')) ? eval($apihook) : false;
	
	// #### print sub nav ##########################################
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'], $_SESSION['ilancedata']['user']['slng']);
	
	// #### display date to and from range #########################
	$reportfromrange = $ilance->admincp->print_from_to_date_range();
	
	$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';
	
	// #### build our sql for transactions #########################
	$sqlinvoicetype = "AND i.invoicetype != 'p2b' AND i.invoicetype != 'buynow'";
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['invoicetype']))
	{
		switch ($ilance->GPC['invoicetype'])
		{
			case 'subscription':
			{
				$sqlinvoicetype = "AND i.invoicetype = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'credential':
			{
				$sqlinvoicetype = "AND i.invoicetype = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'portfolio':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isportfoliofee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'enhancements':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isenhancementfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'fvf':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND isfvf = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'insfee':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isif = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'escrow':
			{
				$sqlinvoicetype = "AND i.isescrowfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'withdraw':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.iswithdrawfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'p2b':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'p2b' AND i.p2b_user_id > 0 AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;        
			}
			case 'p2bfee':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isp2bfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// expenses
			case 'tax':
			{
				$sqlinvoicetype = "AND i.istaxable = '1' AND i.taxamount > 0 AND i.invoicetype != 'credit' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'registerbonus':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'credit' AND i.isregisterbonus = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// loses
			case 'refund':
			{
				$sqlinvoicetype = "AND i.invoicetype = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'cancelled':
			{
				$sqlinvoicetype = "AND i.status = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// disputed
			case 'disputed':
			{
				$sqlinvoicetype = "AND i.indispute = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// nonprofit donation fees collected
			case 'donationfee':
			{
				$sqlinvoicetype = "AND i.isdonationfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// deposits
			case 'deposits':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'credit' AND i.isdeposit = '1'";
				break;
			}
			// withdraws
			case 'withdraws':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.iswithdraw = '1'";
				break;
			}
		}
		
		($apihook = $ilance->api('admincp_accounting_invoicetype_switch_end')) ? eval($apihook) : false;
	}
	
	$sqlinvoiceid = '';
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['invoiceid']) AND $ilance->GPC['invoiceid'] > 0)
	{
		$invid = intval($ilance->GPC['invoiceid']);
		$sqlinvoiceid = "AND i.invoiceid = '" . intval($invid) . "'";
	}
		
	// via txn id?
	$sqlinvoicetxnid = '';
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['transactionid']))
	{
		$sqlinvoicetxnid = "AND i.transactionid = '" . $ilance->db->escape_string($ilance->GPC['transactionid']) . "'";
	}
		
	// invoice status (unpaid showing as default)
	$sqlinvoicestatus = "AND i.status != ''";
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['status']))
	{
		$sqlinvoicestatus = "AND i.status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "'";
	}
	
	$sqlusername = "";
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['username']))
	{
		$sqlusername = "AND c.username LIKE '%" . $ilance->db->escape_string($ilance->GPC['username']) . "%'";
	}
	
	$sqluserid = "";
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['userid']))
	{
		$sqluserid = "AND c.user_id = '" . $ilance->db->escape_string($ilance->GPC['userid']) . "'";
	}
	
	// #### date range exactly as entered
	$sqldaterange = '';
	if (isset($ilance->GPC['range_start']) AND isset($ilance->GPC['range_end']))
	{
		$startdate = print_array_to_datetime($ilance->GPC['range_start']);
		$startdate = substr($startdate, 0, -9);
		
		$enddate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
		$enddate = substr($enddate, 0, -9);
		
		$sqldaterange = " AND (createdate <= '" . $ilance->db->escape_string($enddate) . " " . TIMENOW . "' AND createdate >= '" . $ilance->db->escape_string($startdate) . "')";
	}
	$sqlpricerange = $price_from = $price_to = '';
	if (isset($ilance->GPC['price_from']) AND is_numeric($ilance->GPC['price_from']))
	{
		$price_from = $ilance->db->escape_string($ilance->currency->string_to_number($ilance->GPC['price_from']));
		$sqlpricerange .= " AND i.totalamount >= '" . $price_from . "'";
	}
	if (isset($ilance->GPC['price_to']) AND is_numeric($ilance->GPC['price_to']))
	{
		$price_to = $ilance->db->escape_string($ilance->currency->string_to_number($ilance->GPC['price_to']));
		$sqlpricerange .= " AND i.totalamount <= '" . $price_to . "'";
	}
	$sqlextra = '';
	
	$amounttotal = $paidtotal = $taxtotal = 0;
		
	// #### PAID INVOICES ##################################################
	$pp = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : '10';
	$ilance->GPC['pp'] = $pp;
	$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] < 0) ? 10 : intval($ilance->GPC['pp']);
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	
	$orderlimit = ' ORDER BY i.invoiceid DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilance->GPC['pp']) . ',' . $ilance->GPC['pp'];
	$orderlimit_group_user = ' ORDER BY c.user_id ASC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilance->GPC['pp']) . ',' . $ilance->GPC['pp'];
	$group_by_user = ' GROUP BY user_id';
	
	$sqlpaid = $ilance->db->query("
		SELECT c.user_id, i.last_reminder_sent, c.username, i.invoiceid, i.description, i.amount, i.totalamount, i.paid, i.status, i.invoicetype, i.p2b_user_id, i.createdate, i.duedate, i.paiddate, i.paymethod, i.paymentgateway, i.transactionid, i.p2b_paymethod, i.paymentgateway, i.isdonationfee, i.ischaritypaid, i.currency_id, i.istaxable, i.taxamount, i.taxinfo, l.date_sent, i.isdeposit, i.custommessage, c.available_balance
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		LEFT JOIN " . DB_PREFIX . "invoicelog AS l ON i.invoiceid = l.invoiceid
		WHERE i.archive = '0'
			AND i.paymethod != 'external'
			AND i.invoicetype != 'escrow'
			$sqlinvoicetype
			$sqlinvoicestatus
			$sqlinvoiceid
			$sqlinvoicetxnid
			$sqlusername
			$sqluserid
			$sqldaterange
			$sqlpricerange
			$sqlextra
			$orderlimit
	");
	$sqlpaid2 = $ilance->db->query("
		SELECT c.user_id
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.archive = '0'
			AND i.paymethod != 'external'
			AND i.invoicetype != 'escrow'
			$sqlinvoicetype
			$sqlinvoicestatus
			$sqlinvoiceid
			$sqlinvoicetxnid
			$sqlusername
			$sqluserid
			$sqldaterange
			$sqlpricerange
			$sqlextra
	");
	if ($ilance->db->num_rows($sqlpaid) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sqlpaid, DB_ASSOC))
		{
			$amounttotal += $res['totalamount'];
			$paidtotal += ($res['paid']);

			if ($res['status'] == 'paid' OR $res['status'] == 'complete')
			{
				$res['paid'] = '<div><strong>' . $ilance->currency->format($res['paid'], $res['currency_id']) . '</strong></div>';
				$res['markpaid'] = ($res['isdeposit'] == '1' AND $res['paymethod'] == 'paypal' AND !empty($res['custommessage']) AND $res['status'] == 'paid' AND $res['available_balance'] >= $res['totalamount']) ? '<input type="button" value="{_refund}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_refund_invoice&amp;txn=' . urlencode($res['transactionid']) . '\'" class="buttons" style="font-size:10px" />&nbsp;&nbsp;&nbsp;' : '';
				$res['markunpaid'] = '<input type="button" value="{_mark_as_unpaid}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_mark-invoice-unpaid&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
				$res['status'] = '<div class="black"><strong>{_paid}</strong></div>';
				$res['cancel'] = '<input type="button" value="{_cancel}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_cancel-invoice&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
			}
			else if ($res['status'] == 'unpaid')
			{
				$res['paid'] = '-';
				if($res['invoicetype'] == 'credit')
				{
					$res['markpaid'] = '
					<form name="invoice_' . $res['invoiceid'] . '" method="post" action="' . $ilpage['accounting'] . '" accept-charset="UTF-8" style="margin: 0px;">
						<input type="hidden" name="subcmd" value="_mark-credit-invoice-paid" />
						<input type="hidden" name="id" value="' . $res['invoiceid'] . '" />
						<input type="text" name="amount_' . $res['invoiceid'] . '" value="' . $res['amount'] . '" style="width:50px; font-family: verdana" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="{_paid}" onclick="confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')" class="buttons" style="font-size:10px" />
					</form>';
				}
				else 
				{
					$res['markpaid'] = '<input type="button" value="{_mark_as_paid}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_mark-invoice-paid&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />&nbsp;&nbsp;&nbsp;<input type="button" value="{_send_reminder}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_send_reminder&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
				}

				$res['markunpaid'] = '';
				$res['status'] = '<div class="red"><strong>{_unpaid}</strong></div>';
				$res['cancel'] = '<input type="button" value="{_cancel}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_cancel-invoice&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
			}
			else if ($res['status'] == 'cancelled')
			{
				$res['paid'] = '<del>' . $ilance->currency->format($res['paid'], $res['currency_id']) . '</del>';
				$res['markpaid'] = '';
				$res['markunpaid'] = '';
				$res['status'] = '<strong>{_cancelled}</div>';
				$res['cancel'] = '';
			}
			else if ($res['status'] == 'scheduled')
			{
				$res['paid'] = '-';
				$res['markpaid'] = '<input type="button" value="{_mark_as_paid}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_mark-invoice-paid&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
				$res['markunpaid'] = '';
				$res['status'] = '<div class="red"><strong>{_unpaid}</strong></div>';
				$res['cancel'] = '<input type="button" value="{_cancel}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_cancel-invoice&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
			}
			$res['remove'] = '<input type="button" value="{_remove}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_remove-invoice&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
			$res['amount'] = $ilance->currency->format($res['amount'], $res['currency_id']);
			$res['total'] = $ilance->currency->format($res['totalamount'], $res['currency_id']);
			if ($res['taxamount'] > 0)
			{
				$taxtotal += $res['taxamount'];
				$res['taxamount'] = $ilance->currency->format($res['taxamount'], $res['currency_id']);
			}
			else
			{
				$res['taxamount'] = '-';
			}
			$res['due'] = ($res['duedate'] != "0000-00-00 00:00:00") ? print_date($res['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) : '-';
			$res['paiddate'] = ($res['paiddate'] != "0000-00-00 00:00:00") ? print_date($res['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) : '{_never}';
			$res['createdate'] = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$res['archive'] = '<a href="'.$ilpage['accounting'] . '?subcmd=_archive-invoice&amp;id='.$res['invoiceid'].'" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">{_archive}</a>';
			$res['date_sent'] = (isset($res['last_reminder_sent']) AND $res['last_reminder_sent']!= "0000-00-00 00:00:00") ? print_date($res['last_reminder_sent'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) : '{_no_reminder_sent}';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			if (($res['invoicetype'] == 'p2b' OR $res['invoicetype'] == 'p2bfee') AND $res['user_id'] > 0 AND $res['p2b_user_id'] > 0)
			{
				$invoiceto = fetch_user('username', $res['p2b_user_id']);
				$res['invoicetype'] = '{_generated_by} <span class="blue"><a href="' . $ilpage['subscribers'] . '?cmd=_update-customer&amp;id=' . $res['p2b_user_id'] . '">' . $invoiceto . '</a></span>';
				if ($res['invoicetype'] == 'p2bfee')
				{
					$res['method'] = $ilance->accounting_print->print_paymethod_icon($res['paymethod'], false);
					$res['gateway'] = mb_strtoupper($res['paymentgateway']);
				}
				else
				{
					$res['method'] = ilance_htmlentities($res['p2b_paymethod'], false);
					$res['gateway'] = '{_none}';
				}
			}
			else
			{
				$res['invoicetype'] = ucfirst($res['invoicetype']);
				$res['method'] = $ilance->accounting_print->print_paymethod_icon($res['paymethod'], false);
				if (!empty($res['paymentgateway']))
				{
					$res['gateway'] = mb_strtoupper($res['paymentgateway']);
				}
				else
				{
					$res['gateway'] = '{_none}';
				}
			}

			if ($res['isdonationfee'])
			{
				if ($res['ischaritypaid'])
				{
					$res['extrabutton'] = '&nbsp;&nbsp;<input type="button" value=" {_mark_charity_paid} " onclick="location.href=\'' . HTTP_SERVER_ADMIN . $ilpage['accounting'] . '?subcmd=_mark-charity-paid&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" disabled="disabled" />';
				}
				else
				{
					$res['extrabutton'] = '&nbsp;&nbsp;<input type="button" value=" {_mark_charity_paid} " onclick="location.href=\'' . HTTP_SERVER_ADMIN . $ilpage['accounting'] . '?subcmd=_mark-charity-paid&amp;amount=' . $res['totalamount'] . '&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
				}

				$res['icon'] = '<span style="float:left; padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nonprofits.gif" border="0" alt="" id="" /></span>';
			}
			else
			{
				$res['extrabutton'] = '';
				$res['icon'] = '';
			}

			$invoices[] = $res;
			$row_count++;
		}

		$numberpaid = $ilance->db->num_rows($sqlpaid2);

		$invoiceid = isset($ilance->GPC['invoiceid']) ? intval($ilance->GPC['invoiceid']) : '';
		$transactionid = isset($ilance->GPC['transactionid']) ? intval($ilance->GPC['transactionid']) : '';
		$invoicetype = isset($ilance->GPC['invoicetype']) ? $ilance->GPC['invoicetype'] : '';
		$status = isset($ilance->GPC['status']) ? $ilance->GPC['status'] : '';
		$rangestart0 = isset($ilance->GPC['range_start'][0]) ? $ilance->GPC['range_start'][0] : '01';
		$rangestart1 = isset($ilance->GPC['range_start'][1]) ? $ilance->GPC['range_start'][1] : '01';
		$rangestart2 = isset($ilance->GPC['range_start'][2]) ? $ilance->GPC['range_start'][2] : date("Y");
		$rangeend0 = isset($ilance->GPC['range_end'][0]) ? $ilance->GPC['range_end'][0] : date("m");
		$rangeend1 = isset($ilance->GPC['range_end'][1]) ? $ilance->GPC['range_end'][1] : date("d");
		$rangeend2 = isset($ilance->GPC['range_end'][2]) ? $ilance->GPC['range_end'][2] : date("Y");
		$userid = isset($ilance->GPC['userid']) ? $ilance->GPC['userid'] : '';
		$username = isset($ilance->GPC['username']) ? $ilance->GPC['username'] : '';

		$paidprevnext = print_pagnation($numberpaid, $pp, $ilance->GPC['page'], ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=invoices&amp;invoiceid=" . $invoiceid . "&amp;transactionid=" . $transactionid . "&amp;invoicetype=" . $invoicetype . "&amp;status=" . $status . "&amp;range_start[0]=" . $rangestart0 . "&amp;range_start[1]=" . $rangestart1 . "&amp;range_start[2]=" . $rangestart2 . "&amp;range_end[0]=" . $rangeend0 . "&amp;range_end[1]=" . $rangeend1 . "&amp;range_end[2]=" . $rangeend2 . "&amp;tab=0", 'page');
	}
	else
	{
		$show['no_invoices'] = true;
		$numberpaid = 0;
	}

	$amounttotal = $ilance->currency->format($amounttotal);
	$paidtotal = $ilance->currency->format($paidtotal);
	$taxtotal = $ilance->currency->format($taxtotal);

	// invoice type pulldown
	$ilance->GPC['invoicetype'] = isset($ilance->GPC['invoicetype']) ? $ilance->GPC['invoicetype'] : '';
	$ilance->GPC['status'] = isset($ilance->GPC['status']) ? $ilance->GPC['status'] : '';

	$invoice_type_pulldown = $ilance->admincp->print_invoicetype_pulldown($ilance->GPC['invoicetype']);
	$configuration_invoicesystem  = $ilance->admincp->construct_admin_input('invoicesystem', $ilpage['accounting']);

	// revenue balance sheet
	$revenuebalance = $ilance->admincp->construct_revenue_balance();
	$revenuebalance = array($revenuebalance);
	
	$pprint_array = array('price_from','price_to','username','userid','taxtotal','date_sent','reportfromrange','invid','buildversion','date_sent','ilanceversion','login_include_admin','amounttotal','paidtotal','invoice_status_pulldown','tab','configuration_invoicesystem','numberarchived','archivedprevnext','invoice_type_pulldown','scheduledprevnext','cancelledprevnext','numbercancelled','numberscheduled','numberpaid','numberunpaid','paidprevnext','unpaidprevnext','id','usermodeprevnext');
	
	($apihook = $ilance->api('admincp_accounting_invoices_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'invoices.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', array('invoices','revenuebalance'));
	$ilance->template->parse_loop('main', 'parent_arr');
	if (!isset($parent_arr))
	{
        $parent_arr = array();
	}
	@reset($parent_arr);
	while ($i = @each($parent_arr))
	{
        $ilance->template->parse_loop('main', 'child_arr' . $i['value']['user_id']);
	}
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