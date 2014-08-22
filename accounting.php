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
		'v4',
		'autocomplete',
		'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array('accounting');

// #### setup script location ##################################################
define('LOCATION', 'accounting');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[accounting]" => $ilcrumbs["$ilpage[accounting]"]);

$uncrypted = (isset($ilance->GPC['crypted']) AND !empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : '';
if (!empty($ilance->GPC) AND is_array($ilance->GPC) AND !empty($uncrypted) AND is_array($uncrypted))
{
	$ilance->GPC = array_merge($ilance->GPC, $uncrypted);
}
if (empty($_SESSION['ilancedata']['user']['userid']) OR isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] <= 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['accounting'] . print_hidden_fields(true, array(), true)));
	exit();
}
if (isset($ilance->GPC['cmd']))
{
	// #### FEE CALCULATOR #########################################################
	if ($ilance->GPC['cmd'] == 'feecalculator')
	{
		$area_title = '{_fee_calculator}';
		$page_title = SITE_NAME . ' - {_fee_calculator}';

		($apihook = $ilance->api('accounting_feecalculator_start')) ? eval($apihook) : false;

		$show['finalconversion'] = false;
		$final_conversion = $category_pulldown = $category_pulldown2 = '';
		$cid = isset($ilance->GPC['c']) ? intval($ilance->GPC['c']) : 0;
		$t = isset($ilance->GPC['t']) ? $ilance->GPC['t'] : '';
		$fvf = isset($ilance->GPC['fvf']) ? intval($ilance->GPC['fvf']) : 1;
		$ins = isset($ilance->GPC['ins']) ? intval($ilance->GPC['ins']) : 0;
		$esc = isset($ilance->GPC['esc']) ? intval($ilance->GPC['esc']) : 0;
		$amount = isset($ilance->GPC['amount']) ? $ilance->GPC['amount'] : '';
		$bidamounttype = isset($ilance->GPC['filtered_bidtype']) ? $ilance->GPC['filtered_bidtype'] : '';
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'process' AND isset($ilance->GPC['feetype']) AND isset($ilance->GPC['t']) AND isset($ilance->GPC['amount']))
		{
			$show['finalconversion'] = true;
			$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
			$userid = (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0;
			$final_conversion = $ilance->escrow_fee->fetch_calculated_amount($ilance->GPC['feetype'], $ilance->GPC['amount'], $ilance->GPC['t'], $cid, $bidamounttype, $userid);
			$final_conversion = $ilance->currency->format($final_conversion);
		}
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', '0', '-1');
			$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($cid, 'service', 'level', 'c', 1, $_SESSION['ilancedata']['user']['slng'], 0, '', 0, 1, 0, '540px', 0, 1, 0, false, false, $ilance->categories->cats);
		}
		if ($ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', '0', '-1');
			$category_pulldown2 = $ilance->categories_pulldown->print_cat_pulldown($cid, 'product', 'level', 'c', 1, $_SESSION['ilancedata']['user']['slng'], 0, '', 0, 1, 0, '540px', 0, 1, 0, false, false, $ilance->categories->cats);
		}
		if (isset($t) AND $t != 'productmerchant' AND $t != 'productbuyer')
		{
			$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', '0', '-1');
			$bidamounttype_pulldown = $ilance->auction->construct_bidamounttype_pulldown($bidamounttype, 0, 0, $cid, 'service');
		}
		$pprint_array = array('headerstyle','bidamounttype','bidamounttype_pulldown','t','amount','fvf','ins','esc','final_conversion','category_pulldown','category_pulldown2','cid');

		($apihook = $ilance->api('accounting_feecalculator_end')) ? eval($apihook) : false;

		$ilance->template->load_popup('popupheader', 'popup_header.html');
		$ilance->template->load_popup('popupmain', 'popup_feecalculator.html');
		$ilance->template->load_popup('popupfooter', 'popup_footer.html');
		$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('popupmain', 'increments');
		$ilance->template->parse_if_blocks('popupheader');
		$ilance->template->parse_if_blocks('popupmain');
		$ilance->template->parse_if_blocks('popupfooter');
		$ilance->template->pprint('popupheader', array('headinclude','onbeforeunload','onload','meta_desc','meta_keyw','official_time') );
		$ilance->template->pprint('popupmain', $pprint_array);
		$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
		exit();    
	}
	// #### CURRENCY CONVERTER HANDLER #############################################
	else if ($ilance->GPC['cmd'] == 'currency-converter')
	{
		$page_title = SITE_NAME . ' - {_currency_converter}';
		$area_title = '{_currency_converter}' . ' - ' . SITE_NAME;
		$navcrumb = array();
		$navcrumb[""] = '{_currency_converter}';
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'process' AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['transfer_from']) AND isset($ilance->GPC['transfer_to']))
		{
			($apihook = $ilance->api('accounting_currencyconverter_submit_start')) ? eval($apihook) : false;

			$show['finalconversion'] = true;
			$amount = handle_input_keywords($ilance->GPC['amount']);
			$sql = $ilance->db->query("
				SELECT rate
				FROM " . DB_PREFIX . "currency
				WHERE currency_id = '" . intval($ilance->GPC['transfer_from']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql);
				$sql2 = $ilance->db->query("
					SELECT rate
					FROM " . DB_PREFIX . "currency
					WHERE currency_id = '" . intval($ilance->GPC['transfer_to']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
					$new_transfer_amount = ($amount*$res2['rate']/$res['rate']);
					$final_conversion = '<table width="500" border="0" align="center" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td width="45%" align="right"><h3 style="margin:2px">'.number_format($amount, $ilance->currency->currencies[intval($ilance->GPC['transfer_from'])]['decimal_places'], $ilance->currency->currencies[intval($ilance->GPC['transfer_from'])]['decimal_point'], $ilance->currency->currencies[intval($ilance->GPC['transfer_from'])]['thousands_point']).' '.$ilance->currency->currencies[intval($ilance->GPC['transfer_from'])]['code'].'<!-- WARNING: Automated extraction of data is prohibited under the Terms of Use. --></h3></td>
	<td valign="top" align="center"><h2 style="margin:2px">=</h2></td>
	<td width="45%" align="left"><h3 style="margin:2px">'.number_format($new_transfer_amount, $ilance->currency->currencies[intval($ilance->GPC['transfer_to'])]['decimal_places'], $ilance->currency->currencies[intval($ilance->GPC['transfer_to'])]['decimal_point'], $ilance->currency->currencies[intval($ilance->GPC['transfer_to'])]['thousands_point']).' '.$ilance->currency->currencies[intval($ilance->GPC['transfer_to'])]['code'].'<!-- WARNING: Automated extraction of data is prohibited under the Terms of Use. --></h3></td>
</tr>
<tr>
	<td align="right">'.$ilance->currency->currencies[intval($ilance->GPC['transfer_from'])]['currency_name'].'</td>
	<td valign="top" align="center">&nbsp;</td>
	<td align="left">'.$ilance->currency->currencies[intval($ilance->GPC['transfer_to'])]['currency_name'].'</td>
</tr>
<tr>
	<td align="right"><span class="smaller" style="color:#999">1 '.$ilance->currency->currencies[intval($ilance->GPC['transfer_from'])]['currency_abbrev'].' = '.number_format((1*$res2['rate']/$res['rate']), 6).' '.$ilance->currency->currencies[intval($ilance->GPC['transfer_to'])]['currency_abbrev'].'<!-- WARNING: Automated extraction of data is prohibited under the Terms of Use. --></span></td>
	<td valign="top" align="center">&nbsp;</td>
	<td align="left"><span class="smaller" style="color:#999">1 '.$ilance->currency->currencies[intval($ilance->GPC['transfer_to'])]['currency_abbrev'].' = '.number_format((1*$res['rate']/$res2['rate']), 6).' '.$ilance->currency->currencies[intval($ilance->GPC['transfer_from'])]['currency_abbrev'].'<!-- WARNING: Automated extraction of data is prohibited under the Terms of Use. --></span></td>
</tr>
</table>';
					$transferfrom = $ilance->db->query("
						SELECT currency_id, currency_name, currency_abbrev
						FROM " . DB_PREFIX . "currency
					", 0, null, __FILE__, __LINE__);
					$convert_from_pulldown = '<select name="transfer_from" style="font-family: verdana">';
					$convert_to_pulldown = '<select name="transfer_to" style="font-family: verdana">';	
					while ($row = $ilance->db->fetch_array($transferfrom, DB_ASSOC))
					{
						if (isset($ilance->GPC['transfer_from']) AND $ilance->GPC['transfer_from'] == $row['currency_id'])
						{
							$convert_from_pulldown .= "<option value=\"" . $row['currency_id'] . "\" selected=\"selected\">";	
						}
						else
						{
							$convert_from_pulldown .= "<option value=\"" . $row['currency_id'] . "\">";	
						}
						$convert_from_pulldown .= $row['currency_abbrev'] . ' ' . stripslashes($row['currency_name']);
						$convert_from_pulldown .= "</option>";
						if (isset($ilance->GPC['transfer_to']) AND $ilance->GPC['transfer_to'] == $row['currency_id'])
						{
							$convert_to_pulldown .= "<option value=\"" . $row['currency_id'] . "\" selected=\"selected\">";
						}
						else
						{
							$convert_to_pulldown .= "<option value=\"" . $row['currency_id'] . "\">";
						}
						$convert_to_pulldown .= $row['currency_abbrev'] . ' ' . stripslashes($row['currency_name']);
						$convert_to_pulldown .= "</option>";
					}
					$convert_from_pulldown .= "</select>";
					$convert_to_pulldown .= "</select>";
				}
			}
			$pprint_array = array('convert_from_pulldown','convert_to_pulldown','final_conversion','pb','page_title','cconv','amount','curr_from','convert','into','calcul');

			($apihook = $ilance->api('accounting_currencyconverter_submit_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'accounting_currency_converter.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			($apihook = $ilance->api('accounting_currencyconverter_start')) ? eval($apihook) : false;

			$show['finalconversion'] = false;
			$sql = $ilance->db->query("
				SELECT currency_id, currency_name, currency_abbrev
				FROM " . DB_PREFIX . "currency
			", 0, null, __FILE__, __LINE__);
			$convert_from_pulldown  = '<select name="transfer_from" style="font-family: verdana">';
			while ($row = $ilance->db->fetch_array($sql))
			{
				$convert_from_pulldown .= '<option value="'.$row['currency_id'].'">';
				$convert_from_pulldown .= $row['currency_abbrev'] . ' ' . stripslashes($row['currency_name']);
				$convert_from_pulldown .= '</option>';
			}
			$convert_from_pulldown .= '</select>';
			unset($row);
			$sql2 = $ilance->db->query("
				SELECT currency_id, currency_name, currency_abbrev
				FROM " . DB_PREFIX . "currency
			", 0, null, __FILE__, __LINE__);
			$convert_to_pulldown  = '<select name="transfer_to" style="font-family: verdana">';
			while ($row = $ilance->db->fetch_array($sql2))
			{
				$convert_to_pulldown .= '<option value="'.$row['currency_id'].'">';
				$convert_to_pulldown .= $row['currency_abbrev'] . ' ' . stripslashes($row['currency_name']);
				$convert_to_pulldown .= '</option>';
			}
			$convert_to_pulldown .= '</select>';
			unset($row);
			$pprint_array = array('convert_from_pulldown','convert_to_pulldown','final_conversion','pb','page_title','cconv','amount','curr_from','convert','into','calcul');

			($apihook = $ilance->api('accounting_currencyconverter_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'accounting_currency_converter.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	// #### AUTOPAYMENT ############################################################
	else if ($ilance->GPC['cmd'] == '_do-autopayment-change')
	{
		if (isset($ilance->GPC['autopayment']) AND $ilance->GPC['autopayment'] != '')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET autopayment = '" . intval($ilance->GPC['autopayment']) . "'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
			$area_title = '{_autopayments_preference_changed}';
			$page_title = SITE_NAME . ' - {_autopayments_preference_changed}';
			print_notice('{_autopayments_preference_changed}', '{_you_have_successfully_changed_your_autopayments_setting}', $ilpage['accounting'], '{_my_account}');
			exit();
		}
	}
	// #### CREDIT CARD AUTHENTICATION STEP 1 OF 2 #################################
	else if ($ilance->GPC['cmd'] == '_auth-creditcard' AND isset($ilance->GPC['cc_id']) AND isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'submit')
	{
		$area_title = '{_authenticating_credit_card}';
		$page_title = SITE_NAME . ' - {_authenticating_credit_card}';
		$customer['userid'] = $_SESSION['ilancedata']['user']['userid'];
		$customer['name_on_card'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".intval($ilance->GPC['cc_id'])."'", "name_on_card"));
		$customer['cardid'] = intval($ilance->GPC['cc_id']);
		$customer['address'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".intval($ilance->GPC['cc_id'])."'", "card_billing_address1")) . " " . stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id = ".intval($ilance->GPC['cc_id']), "card_billing_address2"));
		$customer['city'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".intval($ilance->GPC['cc_id'])."'", "card_city"));
		$customer['state'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".intval($ilance->GPC['cc_id'])."'", "card_state"));
		$customer['zip'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".intval($ilance->GPC['cc_id'])."'", "card_postalzip"));
		$customer['country'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = ".$ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".intval($ilance->GPC['cc_id'])."'", "card_country"), "location_".$_SESSION['ilancedata']['user']['slng']));
		$namesplit = explode(' ', $customer['name_on_card']);                        
		$customer['firstname'] = $namesplit[0];
		$customer['lastname'] = $namesplit[1];
		$success = $ilance->accounting_creditcard->creditcard_authentication_step_one($customer['userid'], $customer['cardid'], $customer['firstname'], $customer['lastname'], $customer['address'], $customer['city'], $customer['state'], $customer['zip'], $customer['country']);
		if ($success)
		{
			$area_title = '{_credit_card_authentication_started}';
			$page_title = SITE_NAME . ' - {_credit_card_authentication_started}';
			print_notice('{_credit_card_authentication_started}', '{_you_have_successfully_started_the_credit_card_authentication_process}'."<br /><br />".'{_when_you_are_finished_authenticating_your_card_you_can}'."<br /><br /><li>".'{_deposit_funds_to_your_account_via_credit_card}'."</li><li>".'{_pay_subscription_fees_via_credit_card}'."</li><li>".'{_pay_for_buy_now_solutions_via_credit_card}'."</li>", $ilpage['accounting'], '{_my_account}');
			exit();
		}
	}
	// #### CREDIT CARD AUTHENTICATION STEP 2 OF 2 #################################
	else if ($ilance->GPC['cmd'] == '_auth-creditcard-final' AND isset($ilance->GPC['cc_id']))
	{
		$area_title = '{_final_credit_card_authentication}';
		$page_title = SITE_NAME . ' - {_final_credit_card_authentication}';
		$cc_id = intval($ilance->GPC['cc_id']);
		$sql = $ilance->db->query("
			SELECT creditcard_number, attempt_num
			FROM " . DB_PREFIX . "creditcards
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND cc_id = '".intval($ilance->GPC['cc_id'])."'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$decrypted = $ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
			$ccnum_hidden = substr_replace($decrypted, 'XX XXXX XXXX ', 2 , (mb_strlen($decrypted) - 6));
			$total_cc_attempts = $ilconfig['max_cc_verify_attempts'];
			$cc_attempts_left = ($ilconfig['max_cc_verify_attempts'] - $res['attempt_num']);
			$show['auth_limit'] = true;
			$show['auth_limit_message'] = false;
			if ($res['attempt_num'] >= $ilconfig['max_cc_verify_attempts'])
			{
				$show['auth_limit'] = false;
				$show['auth_limit_message'] = true;
			}
			$headinclude .= '<script type="text/javascript">
<!--
function validateAuthCCFinishForm(f)
{
	haveerrors = 0;
	(f.amount1.value.length < 1) ? showImage("amount1error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("amount1error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
	(f.amount2.value.length < 1) ? showImage("amount2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("amount2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
	return (!haveerrors);
}
// -->
</script>
	';                                
			$ilance->template->fetch('main', 'accounting_authorize_creditcard_finish.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('total_cc_attempts','cc_attempts_left','cc_id','ip','referer','ccnum_hidden'));
			exit();    
		}
	}
	// #### CREDIT CARD AUTHENTICATION STEP 2 OF 2 HANDLER #########################
	else if ($ilance->GPC['cmd'] == '_auth-creditcard-final-process' AND isset($ilance->GPC['cc_id']) AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'submit')
	{	
		$area_title = '{_final_credit_card_authentication}';
		$page_title = SITE_NAME . ' - {_final_credit_card_authentication}';
		$ccid = intval($ilance->GPC['cc_id']);
		$sql_attempt_number = $ilance->db->query("
			SELECT attempt_num
			FROM " . DB_PREFIX . "creditcards
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND cc_id = '".intval($ilance->GPC['cc_id'])."'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_attempt_number) > 0)
		{
			$res_attempt_number = $ilance->db->fetch_array($sql_attempt_number, DB_ASSOC);
			$new_attempt_num = ($res_attempt_number['attempt_num'] + 1);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "creditcards
				SET attempt_num = '".intval($new_attempt_num)."'
				WHERE cc_id = '".intval($ilance->GPC['cc_id'])."'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($new_attempt_num >= $ilconfig['max_cc_verify_attempts'])
			{
				print_notice('{_credit_card_authentication_process_aborted}', '{_were_sorry_you_have_reached_the_maximum_attempt_limit_trying_to_verify_and_authenticate_this_card}'."<br /><br />" . '{_please_contact_customer_support_for_more_information_regarding_the_credit_card_authentication_process}', $ilpage['accounting'], '{_accounting}');
				exit();
			}
			else
			{
				$show['auth_limit'] = true;
				$show['auth_limit_message'] = false;
			}
			$input_auth = ($ilance->GPC['amount1'] + $ilance->GPC['amount2']);
			$v3customer_ccid = $ccid;
			$v3customer_fname = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "name_on_card"));
			$v3customer_lname = '';
			$v3customer_address = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_billing_address1")) . " " . stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_billing_address2"));
			$v3customer_city = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_city"));
			$v3customer_state = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_state"));
			$v3customer_zip = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_postalzip"));
			$v3customer_country = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = ".$ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_country"), "location_" . $_SESSION['ilancedata']['user']['slng']));
			$success = $ilance->accounting_creditcard->creditcard_authentication_step_two($input_auth, $v3customer_ccid, $v3customer_fname, $v3customer_lname, $v3customer_address, $v3customer_city, $v3customer_state, $v3customer_zip, $v3customer_country);
			if ($success)
			{
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('creditcard_authentication_admin');		
				$ilance->email->set(array(
					'{{provider}}' => $_SESSION['ilancedata']['user']['username'],
					'{{amount1}}' => $ilance->GPC['amount1'],
					'{{amount2}}' => $ilance->GPC['amount2'],
					'{{trans1}}' => '[review administration]',
					'{{trans2}}' => '[review administration]',
					'{{cc_id}}' => $v3customer_ccid,
				));
				$ilance->email->send();
				$area_title = '{_credit_card_authentication_complete}';
				$page_title = SITE_NAME . ' - {_credit_card_authentication_complete}';
				print_notice('{_credit_card_authentication_complete}', '{_you_have_successfully_completed_the_credit_debit_card_authentication_process}'."<br />".'{_been_recreditted_back_to_your_online_account}'."<br /><br />".'{_you_can_now}'."<br /><br /><li>".'{_deposit_funds_to_your_account_via_credit_card}'."</li><li>".'{_pay_subscription_fees_via_credit_card}'."</li><li>".'{_pay_for_buy_now_solutions_via_credit_card}'."</li>", $ilpage['accounting'], '{_my_account}');
				exit();
			}
			else
			{
				if ($new_attempt_num >= $ilconfig['max_cc_verify_attempts'])
				{
					// auth amounts do not match and attempt limit exceeded
					$decrypted_card_no = $ilance->crypt->three_layer_decrypt($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid,"creditcard_number"), $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
					$decrypted_card_no = str_replace(' ', '', $decrypted_card_no);
					$name_on_card = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "name_on_card"));
					$namesplit = explode(' ', $name_on_card);
					if ($ilconfig['authentication_refund_on_max_cc_attempts'])
					{
						$v3customer_ccid = $ccid;
						$v3customer_fname = $namesplit[0];
						$v3customer_lname = $namesplit[1];
						$v3customer_address = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_billing_address1")) . " " . stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_billing_address2"));
						$v3customer_city = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_city"));
						$v3customer_state = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_state"));
						$v3customer_zip = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_postalzip"));
						$v3customer_country = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = ".$ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "card_country"), "location_" . $_SESSION['ilancedata']['user']['slng']));
						$input_auth = $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "auth_amount1") + $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "auth_amount2");
						$success = $ilance->accounting_creditcard->creditcard_authentication_refund($input_auth, $v3customer_ccid, $v3customer_fname, $v3customer_lname, $v3customer_address, $v3customer_city, $v3customer_state, $v3customer_zip, $v3customer_country);
						if ($success)
						{
							print_notice('{_credit_card_authentication_amounts_error}', '{_were_sorry_the_amounts_you_have_supplied_does_not_match_the_original_amounts_we_have_debitted_from_your_credit_card}', $ilpage['accounting'], '{_accounting}');
							exit();
						}
						else
						{
							print_notice('{_credit_card_authentication_amounts_error}', '{_were_sorry_the_amounts_you_have_supplied_does_not_match_the_original_amounts_we_have_debitted_from_your_credit_card}', $ilpage['accounting'], '{_accounting}');
							exit();
						}
					}
					else
					{
						$ilance->accounting->insert_transaction(
							0,
							0,
							0,
							$_SESSION['ilancedata']['user']['userid'],
							0,
							0,
							0,
							'{_credit_card_authentication_failure_refund_credit_into_online_account}',
							($sql_cc_arr['auth_amount1']+$sql_cc_arr['auth_amount2']),
							($sql_cc_arr['auth_amount1']+$sql_cc_arr['auth_amount2']),
							'paid',
							'credit',
							'account',
							DATETIME24H,
							DATETIME24H,
							DATETIME24H,
							'{_credit_card_authentication_failure_refund_credit_into_online_account}',
							0,
							0,
							0
						);
						$accountdata = $ilance->accounting->fetch_user_balance($_SESSION['ilancedata']['user']['userid']);
						$new_abalance = $accountdata['available_balance']+($sql_cc_arr['auth_amount1']+$sql_cc_arr['auth_amount2']);
						$new_tbalance = $accountdata['total_balance']+($sql_cc_arr['auth_amount1']+$sql_cc_arr['auth_amount2']);
						// update account balances
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "users
							SET total_balance = '".$new_tbalance."',
							available_balance = '".$new_abalance."'
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
						", 0, null, __FILE__, __LINE__);
						print_notice('{_authenication_process_failed}', '{_you_are_seeing_this_message_because_you_have_exhausted_the_attempts_allowed_for_the_credit_card_authentication_process}', $ilpage['accounting'], '{_accounting}');
						exit();
					}
				}
				print_notice('{_credit_card_authentication_amounts_error}', '{_were_sorry_the_amounts_you_have_supplied_does_not_match_the_original_amounts_we_have_debitted_from_your_credit_card}', $ilpage['accounting'], '{_accounting}');
				exit();
			}    
		}
	}
	// ### Mass Payment Direct #####################################################
	else if ($ilance->GPC['cmd'] == 'transactions' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'directmasspayment' AND isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] != '')
	{
			$area_title = '{_confirming_mass_payment}';
			$page_title = SITE_NAME . ' - {_confirm_mass_payment}';
			$hiddeninvoiceids = '';
			$masspaytotal = $submasspaytotal = $taxtotal = 0;
			$invoices = array();
			$show['leftnav'] = true;
			$mass_payment = $ilance->db->query("
				SELECT invoiceid, invoicetype, transactionid, description, amount, totalamount, taxamount
				FROM " . DB_PREFIX . "invoices
				WHERE projectid = '" . intval($ilance->GPC['projectid'])  . "' AND status = 'unpaid'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($mass_payment) > 0)
			{
				($apihook = $ilance->api('mass_payment_start')) ? eval($apihook) : false;
				
				$ilance->GPC['invoices'] = ((isset($ilance->GPC['invoiceid']) AND is_array($ilance->GPC['invoiceid'])) ? $ilance->GPC['invoiceid'] : array());
				while ($mass_pay = $ilance->db->fetch_array($mass_payment, DB_ASSOC))
				{
					$temp = $mass_pay;
					$hiddeninvoiceids .= '<input type="hidden" name="invoiceid[]" value="' . $mass_pay['invoiceid'] . '" />';
					$temp['amount_formatted'] = $ilance->currency->format($mass_pay['amount']);;
					$temp['total_formatted'] = $ilance->currency->format($mass_pay['totalamount']);
					$temp['tax'] = ($mass_pay['taxamount'] > 0) ? $ilance->currency->format($mass_pay['taxamount']) : '-';
					$temp['txn'] = $mass_pay['transactionid'];
					$temp['invoiceid'] = $mass_pay['invoiceid'];
					$masspaytotal += $temp['totalamount'];
					$submasspaytotal += $temp['amount'];
					$taxtotal += $temp['taxamount'];
					$invoices[] = $temp;
				}
			}
			$total = $masspaytotal;
			$subtotal = $submasspaytotal;
			$total_formatted = $ilance->currency->format($total);
			$subtotal_formatted = $ilance->currency->format($subtotal);
			$taxtotal = $ilance->currency->format($taxtotal);
			unset($masspaytotal);
			$paymethod = $ilance->accounting_print->print_paymethod_pulldown('account', 'account_id', $_SESSION['ilancedata']['user']['userid']);
			$navcrumb = array();
			$navcrumb[HTTPS_SERVER . "$ilpage[main]?cmd=cp"] = '{_my_cp}';
			$navcrumb[HTTPS_SERVER . "$ilpage[accounting]"] = '{_accounting}';
			$navcrumb[HTTPS_SERVER . "$ilpage[accounting]?cmd=transactions"] = '{_transactions}';
			$navcrumb[] = '{_mass_payment}';
			$pprint_array = array('taxtotal','hiddeninvoiceids','paymethod','subtotal_formatted','subtotal','total_formatted','total','prevnext','redirect','referer');

			($apihook = $ilance->api('mass_payment_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'invoicepayment_preview_masspayment.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', 'invoices');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
	}
	// #### MASS PAYMENT FOR UNPAID TRANSACTIONS ###################################
	else if ($ilance->GPC['cmd'] == 'transactions' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'masspayment')
	{
		// #### confirm mass payment ###########################
		if (!isset($ilance->GPC['do']))
		{
			$area_title = '{_confirming_mass_payment}';
			$page_title = SITE_NAME . ' - {_confirm_mass_payment}';

			($apihook = $ilance->api('mass_payment_start')) ? eval($apihook) : false;

			$ilance->GPC['invoices'] = ((isset($ilance->GPC['invoiceid']) AND is_array($ilance->GPC['invoiceid'])) ? $ilance->GPC['invoiceid'] : array());
			// #### error checks ###########################
			if (count($ilance->GPC['invoices']) == 0)
			{
				print_notice('{_no_transactions_selected}', '{_in_order_to_complete_a_masspayment_you_will_need_to}', HTTPS_SERVER . $ilpage['accounting'] . '?cmd=transactions&amp;status=unpaid&amp;pp=50', '{_transactions}');
				exit();
			}
			else if (count($ilance->GPC['invoices']) == 1)
			{
				header('Location: ' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $ilance->GPC['invoices'][0]);
				exit();
			}
			$hiddeninvoiceids = '';
			$masspaytotal = $submasspaytotal = $taxtotal = 0;
			$invoices = array();
			foreach ($ilance->GPC['invoices'] AS $invoiceid)
			{
				$sql = $ilance->db->query("
					SELECT invoicetype, transactionid, description, amount, totalamount, taxamount
					FROM " . DB_PREFIX . "invoices
					WHERE invoiceid = '" . $invoiceid . "'
					LIMIT 1
				");
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddeninvoiceids .= '<input type="hidden" name="invoiceid[]" value="' . $invoiceid . '" />';
				$temp['invoiceid'] = $invoiceid;
				$temp['invoicetype'] = $res['invoicetype'];
				$temp['txn'] = $res['transactionid'];
				$temp['description'] = $res['description'];
				$temp['amount'] = $res['amount'];
				$temp['amount_formatted'] = $ilance->currency->format($res['amount']);
				$temp['total'] = $res['totalamount'];
				$temp['total_formatted'] = $ilance->currency->format($res['totalamount']);
				$tax = $res['taxamount'];
				if ($tax > 0)
				{
					$temp['tax'] = $ilance->currency->format($tax);
					$taxtotal += $tax;
				}
				else
				{
					$temp['tax'] = '-';
				}
				unset($tax);
				$masspaytotal += $temp['total'];
				$submasspaytotal += $temp['amount'];
				$invoices[] = $temp;
			}
			$total = $masspaytotal;
			$subtotal = $submasspaytotal;
			$total_formatted = $ilance->currency->format($total);
			$subtotal_formatted = $ilance->currency->format($subtotal);
			$taxtotal = ($taxtotal > 0) ? $ilance->currency->format($taxtotal) : '-';
			unset($masspaytotal);
			$paymethod = $ilance->accounting_print->print_paymethod_pulldown('account', 'account_id', $_SESSION['ilancedata']['user']['userid']);
			$navcrumb = array();
			$navcrumb[HTTPS_SERVER . "$ilpage[main]?cmd=cp"] = '{_my_cp}';
			$navcrumb[HTTPS_SERVER . "$ilpage[accounting]"] = '{_accounting}';
			$navcrumb[HTTPS_SERVER . "$ilpage[accounting]?cmd=transactions"] = '{_transactions}';
			$navcrumb[] = '{_mass_payment}';
			$pprint_array = array('taxtotal','hiddeninvoiceids','paymethod','subtotal_formatted','subtotal','total_formatted','total','prevnext','redirect','referer');

			($apihook = $ilance->api('mass_payment_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'invoicepayment_preview_masspayment.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', 'invoices');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}

		// #### process mass payment ###########################
		else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'processmasspayment')
		{
			$area_title = '{_mass_payment_completed}';
			$page_title = SITE_NAME . ' - {_mass_payment_completed}';
			if (!isset($ilance->GPC['account_id']) OR empty($ilance->GPC['account_id']))
			{
				$area_title = '{_invoice_payment_menu_denied_payment}';
				$page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_payment}';
				print_notice('{_invoice_error}', '{_no_payment_method_was_selected}', HTTPS_SERVER . $ilpage['accounting'] . '?cmd=transactions', '{_my_account}');
				exit();
			}

			($apihook = $ilance->api('mass_payment_process_start')) ? eval($apihook) : false;

			$ilance->GPC['invoices'] = ((isset($ilance->GPC['invoiceid']) AND is_array($ilance->GPC['invoiceid'])) ? $ilance->GPC['invoiceid'] : array());
			if (count($ilance->GPC['invoices']) == 0)
			{
				print_notice('{_no_transactions_selected}', '{_in_order_to_complete_a_masspayment_you_will_need_to}', HTTPS_SERVER . $ilpage['accounting'] . '?cmd=transactions&amp;status=unpaid&amp;pp=50', '{_transactions}');
				exit();
			}
			else if (count($ilance->GPC['invoices']) == 1)
			{
				header('Location: ' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $ilance->GPC['invoices'][0]);
				exit();
			}
			// #### process invoices and debit account balance
			$failedinvoices = $successinvoices = $failedinvoices_template = '';
			$paymethod = $ilance->GPC['account_id'];
			$masspaytotal = $invoicecount = 0;
			foreach ($ilance->GPC['invoices'] AS $invoiceid)
			{
				// #### process this payment!!
				if ($invoiceid > 0)
				{
					$success = $ilance->accounting_payment->invoice_payment_handler($invoiceid, fetch_invoice('invoicetype', $invoiceid), fetch_invoice('totalamount', $invoiceid), fetch_invoice('user_id', $invoiceid), 'account', '', '', false, '', false);
					if ($success)
					{
						$successinvoices .= '#' . $invoiceid . ', ';
						$masspaytotal += fetch_invoice('totalamount', $invoiceid);
						$invoicecount++;
					}
					else
					{
						$failedinvoices .= '#' . $invoiceid . ', ';
						$failedinvoices_template .= '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $invoiceid . '" target="_blank">#' . $invoiceid . '</a></span>, ';	
					}
				}
			}
			if (!empty($failedinvoices))
			{
				$failedinvoices = substr($failedinvoices, 0, -2);					
				$deposit_funds = '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['accounting'] . '?cmd=deposit">{_deposit_funds}</a></span>';		
				$failedinvoices_template = substr($failedinvoices_template, 0, -2);
				$failedinvoices_template = '{_the_following_transactions_could_not_be_paid_via_mass_payment} ' . $failedinvoices_template . '{_due_to_insufficient_online_funds}.<br /><br />{_proceed_to_deposit_additional_funds_to_complete_the_payment} - ' . $deposit_funds;
				$notice_title = '{_your_mass_payment_was_unsuccessful}';
			}
			else
			{
				$failedinvoices = '{_none}';
				$failedinvoices_template = '{_your_mass_payment_was_processed_and_completed_successfully}';
				$notice_title = '{_mass_payment_completed}';
			}
			$successinvoices = (!empty($successinvoices)) ? substr($successinvoices, 0, -2) : '{_none}';
			$masspaytotal = $ilance->currency->format($masspaytotal);
			$existing = array(
				'{{payer}}' => $_SESSION['ilancedata']['user']['username'],
				'{{payeremail}}' => $_SESSION['ilancedata']['user']['email'],
				'{{invoicecount}}' => $invoicecount,
				'{{masspaytotal}}' => $masspaytotal,					  
				'{{failedinvoices}}' => $failedinvoices,
				'{{successinvoices}}' => $successinvoices,
			);
			$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('mass_payment_complete_payer');		
			$ilance->email->set($existing);
			$ilance->email->send();
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('mass_payment_complete_admin');		
			$ilance->email->set($existing);
			$ilance->email->send();
			$navcrumb = array();
			$navcrumb[HTTPS_SERVER . "$ilpage[main]?cmd=cp"] = '{_my_cp}';
			$navcrumb[HTTPS_SERVER . "$ilpage[accounting]"] = '{_accounting}';
			$navcrumb[HTTPS_SERVER . "$ilpage[accounting]?cmd=transactions"] = '{_transactions}';
			$navcrumb[] = '{_mass_payment_completed}';

			($apihook = $ilance->api('mass_payment_process_end')) ? eval($apihook) : false;

			print_notice($notice_title, $failedinvoices_template, HTTPS_SERVER . $ilpage['accounting'] . '?cmd=transactions&amp;status=unpaid&amp;pp=50', '{_transactions}');
			exit();
		}
	}
	// #### DOWNLOAD CVS ACCOUNTING HISTORY ########################################
	else if ($ilance->GPC['cmd'] == 'transactions' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'request-csv')
	{
		$area_title = '{_downloading_csv_invoice_reports}<div class="smaller>CSV</div>';
		$page_title = SITE_NAME.' - {_downloading_csv_invoice_reports}';

		$csv = '{_invoice_pound}' . ',' .
'{_userid}' . ',' .
'{_description}' . ',' .
'{_amount}' . ',' .
'{_tax}' . ',' .
'{_total}' . ',' .
'{_paid}' . ',' .
'{_currency}' . ',' .
'{_invoice_status}' . ',' .
'{_invoice_type}' . ',' .
'{_pay_method}' . ',' .
'{_ip_address}' . ',' .
'{_create_date}' . ',' .
'{_due_date}' . ',' .
'{_paid_date}' . ',' .
'{_invoice_notes}' . "\n";

		$csv_results = array();
		$csv_query = $ilance->db->query("
			SELECT invoiceid, user_id, description, amount, paid, taxamount, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, currency_id
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND paymethod != 'external' 
			ORDER BY invoiceid DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($csv_query) > 0)
		{
			while ($csv_results = $ilance->db->fetch_array($csv_query, DB_ASSOC))
			{
				$csv .= '"' . $csv_results['invoiceid'] . '",' .
	'"' . $csv_results['user_id'] . '",' .
	'"' . str_replace('"', '\"', stripslashes($csv_results['description'])) . '",' .
	'"' . str_replace('"', '\"', sprintf("%01.2f", $csv_results['amount'])) . '",' .
	'"' . str_replace('"', '\"', sprintf("%01.2f", $csv_results['taxamount'])) . '",' .
	'"' . str_replace('"', '\"', sprintf("%01.2f", $csv_results['totalamount'])) . '",' .
	'"' . str_replace('"', '\"', sprintf("%01.2f", $csv_results['paid'])) . '",' .
	'"' . str_replace('"', '\"', (($csv_results['currency_id'] == '0') ? print_left_currency_symbol() : $ilance->currency->currencies[$csv_results['currency_id']]['symbol_left'])) . '",' .
	'"{_' . $csv_results['status'] . '}",' .
	'"' . $ilance->accounting_print->print_transaction_type($csv_results['invoicetype']) . '",' .
	'"{_' . $csv_results['paymethod'] . '}",' .
	'"' . $csv_results['ipaddress'] . '",' .
	'"' . $csv_results['createdate'] . '",' .
	'"' . $csv_results['duedate'] . '",' .
	'"' . $csv_results['paiddate'] . '",' .
	'"' . str_replace('"', '\"', stripslashes($csv_results['custommessage'])) . '"' . "\n";
			}

			$ilance->template->templateregistry['csvtxn'] = $csv;
			$ilance->common->download_file($ilance->template->parse_template_phrases('csvtxn'), "transactions-" . date('Y') . "-" . date('m') . "-" . date('d') . ".csv", "text/plain");
			exit();    
		}
	}
	// #### TRANSACTIONS HISTORY ###################################################
	else if ($ilance->GPC['cmd'] == 'transactions')
	{
		$show['widescreen'] = false;
		$show['leftnav'] = true;
		$area_title = '{_transactions_history}';
		$page_title = SITE_NAME . ' - {_transactions_history}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[accounting]"] = '{_accounting}';
		$navcrumb[""] = '{_transactions}';

		($apihook = $ilance->api('accounting_transactionhistory_start')) ? eval($apihook) : false;

		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		$extra = '';
		require_once(DIR_CORE . 'functions_search.php');
		$statuses = array('paid', 'unpaid', 'overdue', 'cancelled');
		$ilance->GPC['status'] = (isset($ilance->GPC['status']) ? $ilance->GPC['status'] : '');
		$statussql = '';
		if (isset($ilance->GPC['status']) AND in_array($ilance->GPC['status'], $statuses))
		{
			if ($ilance->GPC['status'] == 'overdue')
			{
				$statussql = "AND duedate <= '" . DATETIME24H . "' AND status = 'unpaid'";
			}
			else
			{
				$statussql = "AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "'";
			}
		}

		$extra .= '&amp;status=' . $ilance->GPC['status'];

		// #### transaction type #############################
		$types = array('debit', 'credit', 'subscription', 'commission', 'p2b', 'credential');
		$ilance->GPC['invoicetype'] = (isset($ilance->GPC['invoicetype']) ? $ilance->GPC['invoicetype'] : '');
		$typesql = "AND invoicetype != 'escrow'";
		$extra .= '&amp;invoicetype=' . $ilance->GPC['invoicetype'];
		$userextra = empty($ilance->GPC['invoicetype']) ? "AND (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')" : "AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'";
		if (isset($ilance->GPC['invoicetype']) AND in_array($ilance->GPC['invoicetype'], $types))
		{
			$typesql = "AND invoicetype = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND invoicetype != 'escrow'";
			if ($ilance->GPC['invoicetype'] == 'p2b')
			{
				$userextra = "AND (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')";
			}
		}

		// #### transaction period #############################
		$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
		$extra .= '&amp;period=' . $ilance->GPC['period'];
		$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'createdate', '>=');

		// #### ordering by fields defaults ####################
		$orderbyfields = array('invoiceid', 'amount', 'paid');
		$orderby = '&amp;orderby=invoiceid';
		$orderbysql = 'invoiceid';
		if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
		{
			$orderby = '&amp;orderby=' . $ilance->GPC['orderby'];
			$orderbysql = $ilance->GPC['orderby'];
		}

		// #### is final value fee quick search ################
		$ilance->GPC['isfvf'] = (isset($ilance->GPC['isfvf']) ? 1 : 0);
		$isfvfsql = (isset($ilance->GPC['isfvf']) AND $ilance->GPC['isfvf']) ? "AND isfvf = '1'" : '';
		$extra .= (isset($ilance->GPC['isfvf']) AND $ilance->GPC['isfvf']) ? '&amp;isfvf=' . $ilance->GPC['isfvf'] : '';

		// #### display order defaults #########################
		$displayorderfields = array('asc', 'desc');
		$displayorder = '&amp;displayorder=desc';
		$currentdisplayorder = $displayorder;
		$displayordersql = 'DESC';
		if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
		{
			$displayorder = '&amp;displayorder=desc';
			$currentdisplayorder = '&amp;displayorder=asc';
		}
		else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
		{
			$displayorder = '&amp;displayorder=asc';
			$currentdisplayorder = '&amp;displayorder=desc';
		}
		if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
		{
			$displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
		}
		$ilance->GPC['pp'] = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
		if ($ilance->GPC['pp'] <= 0)
		{
			$ilance->GPC['pp'] = $ilconfig['globalfilters_maxrowsdisplay'];
		}
		$limit = ' ORDER BY ' . $orderbysql . ' ' . $displayordersql . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilance->GPC['pp']) . ',' . $ilance->GPC['pp'];
		$php_self = $ilpage['accounting'] . '?cmd=transactions' . $displayorder . $extra;
		$scriptpage = $ilpage['accounting'] . '?cmd=transactions' . $currentdisplayorder . $orderby . $extra;
		$cntexe = $ilance->db->query("
			SELECT COUNT(*) AS number
			FROM " . DB_PREFIX . "invoices
			WHERE paymethod != 'external'
				$typesql
				$userextra
				$periodsql
				$statussql
				$isfvfsql
				AND status != 'scheduled'
		", 0, null, __FILE__, __LINE__);
		$cntarr = $ilance->db->fetch_array($cntexe);
		$number = intval($cntarr['number']);
		$counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];
		$res = $ilance->db->query("
			SELECT user_id, description, createdate, duedate, paiddate, status, invoicetype, totalamount, amount, taxamount, invoiceid, transactionid, paid, p2b_user_id, projectid, paymethod, currency_id
			FROM " . DB_PREFIX . "invoices
			WHERE paymethod != 'external'
				$typesql
				$userextra
				$periodsql
				$statussql
				$isfvfsql
				AND status != 'scheduled'
			$limit
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($res) > 0)
		{
			$altrows = 0;
			while ($row = $ilance->db->fetch_array($res, DB_ASSOC))
			{
				$altrows++;
				$row['class'] = (floor($altrows / 2) == ($altrows / 2)) ? 'alt2' : 'alt1';
				if ($row['invoicetype'] == 'p2b')
				{
					if (fetch_project_ownerid($row['projectid']) == $_SESSION['ilancedata']['user']['userid'])
					{
						$row['sym'] = '-';
						$row['invoicetype'] = '{_generated_by}' . ' <span class="blue">' . print_username($row['p2b_user_id'], 'href', 0, '&amp;feedback=1', '') . '</span>';
						$row['action'] = ($row['status'] == 'unpaid') ? '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $row['transactionid'] . '" class="buttons"><strong>{_pay_now}</strong></a>' : '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $row['transactionid'] . '" class="buttons">{_view}</a>';
					}
					else
					{
						$row['sym'] = '+';
						$row['invoicetype'] = '{_generated_to}' . ' <span class="blue">' . print_username($row['user_id'], 'href', 0, '&amp;feedback=1', '') . '</span>';
						$row['action'] = ($row['status'] == 'unpaid') ? '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $row['transactionid'] . '" class="buttons"><strong>{_pay_now}</strong></a>' : '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $row['transactionid'] . '" class="buttons">{_review}</a>';
					}
					$row['tax'] = $ilance->currency->format($row['taxamount'], $row['currency_id']);
					$row['amount'] = $ilance->currency->format($row['amount'], $row['currency_id']);
					$row['total'] = $ilance->currency->format($row['totalamount'], $row['currency_id']);
					$row['paid'] = $ilance->currency->format($row['paid'], $row['currency_id']);
					$row['cb'] = '<input type="checkbox" name="invoiceid[]" value="' . $row['invoiceid'] . '" disabled="disabled" />';
				}
				else
				{
					$row['action'] = ($row['status'] == 'unpaid') ? '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $row['transactionid'] . '" class="buttons"><strong>{_pay_now}</strong></a>' : '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $row['transactionid'] . '" class="buttons">{_view}</a>';
					switch ($row['invoicetype'])
					{
						case 'storesubscription':
						case 'subscription':
						case 'commission':
						case 'buynow':
						case 'credential':
						case 'debit':
						case 'escrow':
						{
							$row['sym'] = '-';
							if ($row['taxamount'] > 0)
							{
								$row['tax'] = $ilance->currency->format($row['taxamount'], $row['currency_id']);
							}
							else
							{
								$row['tax'] = '-';
							}

							$row['amount'] = $ilance->currency->format($row['amount'], $row['currency_id']);
							$row['total'] = $ilance->currency->format($row['totalamount'], $row['currency_id']);
							$row['paid']   = $ilance->currency->format($row['paid'], $row['currency_id']);
							break;
						}
						case 'credit':
						{
							$row['sym'] = '+';
							if ($row['taxamount'] > 0)
							{
								$row['tax'] = $ilance->currency->format($row['taxamount'], $row['currency_id']);
							}
							else
							{
								$row['tax'] = '-';
							}
							$row['amount'] = $ilance->currency->format($row['amount'], $row['currency_id']);
							$row['total'] = $ilance->currency->format($row['totalamount'], $row['currency_id']);
							$row['paid']   = $ilance->currency->format($row['paid'], $row['currency_id']);
							$row['action'] =($row['status'] == 'unpaid') ? '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $row['transactionid'] . '" class="buttons"><strong>{_pay_now}</strong></a>' : '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $row['transactionid'] . '" class="buttons"><strong>{_view}</strong></a>';
							break;
						}                                                       

					}

					$row['invoicetype'] = $ilance->accounting_print->print_transaction_type($row['invoicetype']);
				}

				$row['description'] = stripslashes($row['description']);
				$row['createdate'] = print_date($row['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$row['duedate'] = ($row['duedate'] == '0000-00-00 00:00:00') ? '-' : print_date($row['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);

				switch ($row['status'])
				{
					case 'paid':
					{
						$row['status'] = '{_paid}';
						$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_checkmark.gif" border="0" alt="" />';
						$row['cb'] = '<input type="checkbox" name="invoiceid[]" value="' . $row['invoiceid'] . '" disabled="disabled" />';
						$row['paiddate'] = ($row['paiddate'] == '0000-00-00 00:00:00') ? '-' : print_date($row['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						break;
					}
					case 'unpaid':
					{
						$row['status'] = '{_unpaid}';
						$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice.gif" border="0" alt="" />';
						$row['cb'] = '<input type="checkbox" name="invoiceid[]" value="' . $row['invoiceid'] . '" />';
						$row['paiddate'] ='-';
						break;
					}
					case 'scheduled':
					{
						$row['status'] = '{_scheduled}';
						$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice.gif" border="0" alt="" />';
						$row['cb'] = '<input type="checkbox" name="invoiceid[]" value="' . $row['invoiceid'] . '" disabled="disabled" />';
						break;
					}
					case 'complete':
					{
						$row['status'] = '{_complete}';
						$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_checkmark.gif" border="0" alt="" />';
						$row['cb'] = '<input type="checkbox" name="invoiceid[]" value="' . $row['invoiceid'] . '" disabled="disabled" />';
						break;
					}
					case 'cancelled':
					{
						$row['status'] = '{_cancelled}';
						$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_gray.gif" border="0" alt="" />';
						$row['cb'] = '<input type="checkbox" name="invoiceid[]" value="' . $row['invoiceid'] . '" disabled="disabled" />';
						break;
					}
				}

				$transaction_rows[] = $row;
			}

			$show['no_rows_returned'] = false;
		}
		else
		{
			$show['no_rows_returned'] = true;
		}
		$prevnext = print_pagnation($number, $ilance->GPC['pp'], $ilance->GPC['page'], $counter, $scriptpage);
		$pprint_array = array('php_self','page','prevnext');

		($apihook = $ilance->api('accounting_transactionhistory_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_transactions.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'transaction_rows');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### SCHEDULED TRANSACTIONS MENU ############################################
	else if ($ilance->GPC['cmd'] == 'sch-transactions')
	{
		$show['widescreen'] = false;
		$show['leftnav'] = true;
		$area_title = '{_scheduled_transactions_history}';
		$page_title = SITE_NAME . ' - {_scheduled_transactions_history}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[accounting]"] = '{_accounting}';
		$navcrumb[""] = '{_scheduled_transactions}';

		($apihook = $ilance->api('accounting_scheduledtransactionhistory_start')) ? eval($apihook) : false;

		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		$limit = ' ORDER by createdate DESC LIMIT '.(($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay']).','.$ilconfig['globalfilters_maxrowsdisplay'];
		$cntarr = array();
		$cntexe = $ilance->db->query("
			SELECT COUNT(*) AS number
			FROM " . DB_PREFIX . "invoices
			WHERE (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
				AND status = 'scheduled'
		", 0, null, __FILE__, __LINE__);
		$cntarr = $ilance->db->fetch_array($cntexe);
		$number = $cntarr['number'];
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
		$row_count = 0;
		$res = $ilance->db->query("
			SELECT createdate, duedate, paiddate, totalamount, paid, description, status, invoiceid, transactionid, paymethod, invoicetype
			FROM " . DB_PREFIX . "invoices
			WHERE (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
				AND status = 'scheduled'
			$limit
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($res) > 0)
		{
			while ($row = $ilance->db->fetch_array($res, DB_ASSOC))
			{
				$row['createdate'] = print_date($row['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				if ($row['duedate'] == '0000-00-00 00:00:00')
				{
					$row['duedate'] = '--';
				}
				else
				{
					$row['duedate'] = print_date($row['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				if ($row['paiddate'] == '0000-00-00 00:00:00')
				{
					$row['paiddate'] = '--';
				}
				else
				{
					$row['paiddate'] = print_date($row['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				$row['amount'] = $ilance->currency->format($row['totalamount']);
				$row['paid'] = $ilance->currency->format($row['paid']);
				if ($row['status'] == 'paid')
				{
					$row['action'] = '-';
					$row['status'] = '{_paid}';
				}
				else if ($row['status'] == 'unpaid')
				{
					$row['action'] = "<a href='".$ilpage['invoicepayment']."?id=".$row['invoiceid']."' target='_self'><img src='".$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder']."icons/invoice.gif' border=0 alt=''></a>";
					$row['status'] = '{_pending}';
				}
				else if ($row['status'] == 'scheduled' AND $row['invoicetype'] == 'subscription')
				{
					$row['action'] = "<a href='".$ilpage['invoicepayment']."?id=".$row['invoiceid']."' target='_self'><img src='".$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder']."icons/invoice.gif' border=0 alt=''></a>";
					$row['status'] = '{_pending}';
				}
				else if ($row['status'] == 'scheduled' AND $row['invoicetype'] == 'debit' AND $row['paymethod'] != 'check')
				{
					$row['action'] = '-';
					$row['status'] = '{_processing}';
				}
				else if ($row['status'] == 'scheduled' AND $row['invoicetype'] == "debit" AND $row['paymethod'] == 'check')
				{
					$row['action'] = '-';
					$row['status'] = '{_processing}';
				}
				else if ($row['status'] == 'scheduled' AND $row['invoicetype'] == 'credit')
				{
					$row['action'] = "<a href='".$ilpage['invoicepayment']."?id=".$row['invoiceid']."' target='_self'>".'{_cancel}'."</a>";
					$row['status'] = '{_processing}';
				}
				else if ($row['status'] == 'complete')
				{
					$row['action'] = '-';
					$row['status'] = '{_finished}';
				}
				else if ($row['status'] == 'cancelled')
				{
					$row['action'] = '-';
					$row['status'] = '{_cancelled}';
				}
				// we don't know this type of invoice at this moment
				// http://www.ilance.com/forum/project.php?issueid=1883
				if (!isset($row['action'])) 
				{
					$row['action'] = '-';
					$row['status'] = '{_' . $row['status'] . '}';
				}
				$row['paymethod'] = $ilance->accounting_print->print_paymethod_icon($row['paymethod'], false);
				$row['paysource'] = '{_account}';
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$schtransaction_rows[] = $row;
				$row_count++;
			}
		}
		else
		{
			$show['no_rows_returned'] = true;
		}
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['accounting'].'?cmd=sch-transactions');
		$pprint_array = array('page','prevnext');

		($apihook = $ilance->api('accounting_scheduledtransactionhistory_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_schtransactions.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'schtransaction_rows');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### DEPOSIT FUNDS MENU #####################################################
	else if ($ilance->GPC['cmd'] == 'deposit')
	{
		$show['widescreen'] = false;
		$topnavlink = array(
			'deposit',
		);
		$show['leftnav'] = true;
		if (empty($_SESSION['ilancedata']['user']['active']) OR $_SESSION['ilancedata']['user']['active'] == 'no' OR $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'deposit') == 'no')
		{
			$area_title = '{_access_denied_to_deposit_funds}';
			$page_title = SITE_NAME . ' - {_access_denied_to_deposit_funds}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . ' <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('deposit'));
			exit();
		}
		$show['deposit_errors'] = false;
		if (isset($ilance->GPC['err']) AND $ilance->GPC['err'] == '_yes')
		{
			$show['deposit_errors'] = true;
		}
		$area_title = '{_deposit_funds_menu}';
		$page_title = SITE_NAME . ' - {_deposit_funds_menu}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[accounting]"] = '{_accounting}';
		$navcrumb[""] = '{_deposit_funds}';

		($apihook = $ilance->api('accounting_deposit_start')) ? eval($apihook) : false;

		// javascript
		$headinclude .= '<script type="text/javascript">
<!--
function validate_deposit_form(f)
{
	haveerrors = 0;
	(f.deposit_amount.value.length < 1) ? showImage("deposit_amounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("deposit_amounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
	if (f.account_id.options[f.account_id.selectedIndex].value == \'paypal\' || f.account_id.options[f.account_id.selectedIndex].value == \'paypalecheck\')
	{
		if (f.ppemail.value == \'\')
		{
			haveerrors = 1;
			showImage("paypalemailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true);
		}
	}
	return (!haveerrors);
}
function clearvalues()
{
	var oDeposit = fetch_js_object("objTotalFees");
	var oGateway = fetch_js_object("objTotalGWFees");
	oDeposit.innerHTML = "";
	oGateway.innerHTML = "";
	document.deposit.deposit_amount.value = "";
}
function show_paypal_options(pSelected, target)
{
	obj = fetch_js_object(target);
	if (pSelected == \'paypal\')
	{
		obj.style.display = \'inline\';
	}
	else if (pSelected == \'paypalecheck\')
	{
		obj.style.display = \'inline\';
	}
	else if (pSelected == parseInt(pSelected))
	{
		obj.style.display = \'none\';
	}
	else
	{
		obj.style.display = \'none\';
	}
}
function show_creditcard_options(pSelected, target)
{
	obj = fetch_js_object(target);
	if (pSelected == \'ccform\')
	{
		obj.style.display = \'inline\';
	}
	else
	{
		obj.style.display = \'none\';
	}
}
function show_deposit_amount(pSelected)
{
	max = fetch_js_object(\'max_deposit_amount\');
	min = fetch_js_object(\'min_deposit_amount\');
	if (pSelected.substr(0,7) == "offline")
	{
		min.innerHTML = "' . strip_tags($ilance->currency->format($ilconfig['invoicesystem_minofflinedepositamount'])) . '";
		max.innerHTML = "' . strip_tags($ilance->currency->format($ilconfig['invoicesystem_maxofflinedepositamount'])) . '";
	}
	else
	{
		min.innerHTML = "' . strip_tags($ilance->currency->format($ilconfig['invoicesystem_mindepositamount'])) . '";
		max.innerHTML = "' . strip_tags($ilance->currency->format($ilconfig['invoicesystem_maxdepositamount'])) . '";
	}
}
var deposit_fees = new Array();
	';			

		if ($ilconfig['invoicesystem_showlivedepositfees'])
		{
			$headinclude .= '
function calculator(pAmount, pForm, pSelected)
{
	var oDeposit = fetch_js_object("objTotalFees");
	var oGateway = fetch_js_object("objTotalGWFees");
	gAmount = Number(pAmount.value)
	if (pSelected == \'\')
	{
		fee_a = 0;
		fee_b = 0;
	}
	else
	{
		if (pSelected == \'paypal\')
		{
			fee_a = ' . floatval($ilconfig['paypal_transaction_fee']) . ';
			fee_b = ' . floatval($ilconfig['paypal_transaction_fee2']) . ';
			feebit = " ( ' . (floatval($ilconfig['paypal_transaction_fee']) * 100) . '% + ' . strip_tags($ilance->currency->format($ilconfig['paypal_transaction_fee2'])) . ' )";
			gFee = (gAmount*fee_a) + fee_b;
		}
		else if (pSelected == \'paypalecheck\')
		{
			fee_a = ' . floatval($ilconfig['paypal_deposit_echeck_fee']) . ';
			fee_b = 0;
			feebit = "";
			gFee = fee_a;
		}
		else if (pSelected == \'paypal_pro\')
		{
			fee_a = ' . floatval($ilconfig['paypal_pro_transaction_fee']) . ';
			fee_b = ' . floatval($ilconfig['paypal_pro_transaction_fee2']) . ';
			feebit = " ( ' . (floatval($ilconfig['paypal_pro_transaction_fee']) * 100) . '% + ' . strip_tags($ilance->currency->format($ilconfig['paypal_pro_transaction_fee2'])) . ' )";
			gFee = (gAmount*fee_a) + fee_b;
		}
		else if (pSelected == \'cashu\')
		{
			fee_a = ' . floatval($ilconfig['cashu_transaction_fee']) . ';
			fee_b = ' . floatval($ilconfig['cashu_transaction_fee2']) . ';
			feebit = " ( ' . (floatval($ilconfig['cashu_transaction_fee']) * 100) . '% + ' . strip_tags($ilance->currency->format($ilconfig['cashu_transaction_fee2'])) . ' )";
			gFee = (gAmount*fee_a) + fee_b;
		}
		else if (pSelected == \'moneybookers\')
		{
			fee_a = ' . floatval($ilconfig['moneybookers_transaction_fee']) . ';
			fee_b = ' . floatval($ilconfig['moneybookers_transaction_fee2']) . ';
			feebit = " ( ' . (floatval($ilconfig['moneybookers_transaction_fee']) * 100) . '% + ' . strip_tags($ilance->currency->format($ilconfig['moneybookers_transaction_fee2'])) . ' )";
			gFee = (gAmount*fee_a) + fee_b;
		}
		else if (pSelected == \'platnosci\')
		{
			fee_a = ' . floatval($ilconfig['platnosci_transaction_fee']) . ';
			fee_b = ' . floatval($ilconfig['platnosci_transaction_fee2']) . ';
			feebit = " ( ' . (floatval($ilconfig['platnosci_transaction_fee']) * 100) . '% + ' . strip_tags($ilance->currency->format($ilconfig['platnosci_transaction_fee2'])) . ' )";
			gFee = (gAmount*fee_a) + fee_b;
		}
		else if (pSelected == \'ccform\')
		{
			fee_a = ' . floatval($ilconfig['cc_transaction_fee']) . ';
			fee_b = ' . floatval($ilconfig['cc_transaction_fee2']) . ';
			feebit = "";
			gFee = (gAmount*fee_a) + fee_b;
		}
		else if (pSelected == parseInt(pSelected))
		{
			fee_a = ' . floatval($ilconfig['cc_transaction_fee']) . ';
			fee_b = ' . floatval($ilconfig['cc_transaction_fee2']) . ';
			feebit = " ( ' . (floatval($ilconfig['cc_transaction_fee']) * 100) . '% + ' . strip_tags($ilance->currency->format($ilconfig['cc_transaction_fee2'])) . ' )";
			gFee = (gAmount*fee_a) + fee_b;
		}
		else if (pSelected.substr(0,7) == "offline")
		{
			fee_a = deposit_fees[pSelected.substr(8)];
			fee_b = 0.00;
			feebit = "";
			gFee = (fee_a) + fee_b;
		}
	}
	if (isNaN(gAmount) || gAmount == 0)
	{
		document.deposit.deposit_amount.value = "";
		oDeposit.innerHTML = "0.00";
		oGateway.innerHTML = "0.00";
	} 
	else 
	{
		gFee = gFee.toFixed(2);
		oGateway.innerHTML = gFee + feebit;
		gDeposit = parseFloat(gAmount) + parseFloat(gFee);
		gDeposit = gDeposit.toFixed(2);
		document.deposit.deposit_amount2.value = parseFloat(gAmount) + parseFloat(gFee);
		oDeposit.innerHTML = gDeposit;
	}		
}
//-->
</script>';
		}
		else
		{
			$headinclude .= '
function calculator(pAmount, pForm, pSelected)
{
	var oDeposit = fetch_js_object("objTotalFees");
	var oGateway = fetch_js_object("objTotalGWFees");
	gAmount = Number(pAmount.value);
	if (pSelected == \'\')
	{
		fee_a = 0;
		fee_b = 0;
	}
	else
	{
		if (pSelected == \'paypal\')
		{
			fee_a = ' . $ilconfig['paypal_transaction_fee'] . ';
			fee_b = ' . $ilconfig['paypal_transaction_fee2'] . ';
			feebit = "";
			gFee = (gAmount*fee_a) + fee_b;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
		else if (pSelected == \'paypalecheck\')
		{
			fee_a = ' . $ilconfig['paypal_deposit_echeck_fee'] . ';
			fee_b = 0;
			feebit = "";
			gFee = fee_a;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
		else if (pSelected == \'paypal_pro\')
		{
			fee_a = ' . $ilconfig['paypal_pro_transaction_fee'] . ';
			fee_b = ' . $ilconfig['paypal_pro_transaction_fee2'] . ';
			feebit = "";
			gFee = (gAmount*fee_a) + fee_b;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
		else if (pSelected == \'cashu\')
		{
			fee_a = ' . $ilconfig['cashu_transaction_fee'] . ';
			fee_b = ' . $ilconfig['cashu_transaction_fee2'] . ';
			feebit = "";
			gFee = (gAmount*fee_a) + fee_b;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
		else if (pSelected == \'moneybookers\')
		{
			fee_a = ' . $ilconfig['moneybookers_transaction_fee'] . ';
			fee_b = ' . $ilconfig['moneybookers_transaction_fee2'] . ';
			feebit = "";
			gFee = (gAmount*fee_a) + fee_b;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
		else if (pSelected == \'platnosci\')
		{
			fee_a = ' . $ilconfig['platnosci_transaction_fee'] . ';
			fee_b = ' . $ilconfig['platnosci_transaction_fee2'] . ';
			feebit = "";
			gFee = (gAmount*fee_a) + fee_b;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
		else if (pSelected == \'ccform\')
		{
			fee_a = ' . $ilconfig['cc_transaction_fee'] . ';
			fee_b = ' . $ilconfig['cc_transaction_fee2'] . ';
			feebit = "";
			gFee = (gAmount*fee_a) + fee_b;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
		else if (pSelected == parseInt(pSelected))
		{
			fee_a = ' . $ilconfig['cc_transaction_fee'] . ';
			fee_b = ' . $ilconfig['cc_transaction_fee2'] . ';
			feebit = "";
			gFee = (gAmount*fee_a) + fee_b;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
		else if (pSelected.substr(0,7) == "offline")
		{
			fee_a = deposit_fees[pSelected.substr(8)];
			fee_b = 0.00;
			feebit = "";
			gFee = (fee_a) + fee_b;
			gFeeTotal = gAmount + gFee;
			gFeeTotal = gFeeTotal.toFixed(2);
		}
	}
	if (isNaN(gAmount) || gAmount == 0)
	{
		document.deposit.deposit_amount.value = "";
		document.deposit.deposit_amount2.value = "";
		oDeposit.innerHTML = "";
		oGateway.innerHTML = "";
	} 
	else 
	{
		gFee = gFee.toFixed(2);
		oGateway.innerHTML = "";
		gDeposit = parseFloat(gAmount) + parseFloat(gFee);
		gDeposit = gDeposit.toFixed(2);
		document.deposit.deposit_amount2.value = parseFloat(gAmount) + parseFloat(gFee);
		oDeposit.innerHTML = "";
	}		
}
//-->
</script>';
		}

		$javascript = '
onchange="if (account_id.options[account_id.selectedIndex].value != \'\') 
{ 
	toggle_paid(\'depositoptions\'); 
	clearvalues(); 
	show_paypal_options(account_id.options[account_id.selectedIndex].value, \'paypaloptions\'); 
	show_creditcard_options(account_id.options[account_id.selectedIndex].value, \'creditcardoptions\'); 
	show_deposit_amount(account_id.options[account_id.selectedIndex].value);
} 
else 
{ 
	toggle_tr(\'depositoptions\'); 
	show_paypal_options(account_id.options[account_id.selectedIndex].value, \'paypaloptions\'); 
	show_creditcard_options(account_id.options[account_id.selectedIndex].value, \'creditcardoptions\'); 
	show_deposit_amount(account_id.options[account_id.selectedIndex].value);
}"';

		$deposit_method_pulldown = $ilance->accounting_print->print_paymethod_pulldown('deposit', 'account_id', $_SESSION['ilancedata']['user']['userid'], $javascript);
		$cc_type_pulldown = $ilance->accounting->creditcard_type_pulldown('', 'creditcard_type');
		$show['paypal_pro'] = $ilconfig['use_internal_gateway'] == 'paypal_pro' ? true : false;
		$currency = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'];
		$returnurl = isset($ilance->GPC['returnurl']) ? urlencode($ilance->GPC['returnurl']) : '';
		$pprint_array = array('returnurl','cc_type_pulldown','currency','nav_menu','max_deposit_amount','accsum','chodep','accou','depam','demeth','click','todep','defund','depfor','belo','ifyou','charg','nonr','paypal','mini','stat','avail','totbal','deposit_method_pulldown','min_deposit_amount','referer','ip','deposit_content','account_number','available_balance','total_balance','status');

		($apihook = $ilance->api('accounting_deposit_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_deposit.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'schtransaction_rows');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### DEPOSIT FUNDS PREVIEW ##################################################
	else if ($ilance->GPC['cmd']== '_secure-deposit' AND isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'preview' AND isset($ilance->GPC['account_id']))
	{
		if (substr($ilance->GPC['account_id'], 0, 7) == 'offline')
		{
			if (empty($ilance->GPC['deposit_amount']) OR $ilance->GPC['deposit_amount'] < $ilconfig['invoicesystem_minofflinedepositamount'] OR $ilance->GPC['deposit_amount'] > $ilconfig['invoicesystem_maxofflinedepositamount'])
			{ 
				refresh($ilpage['accounting'] . '?cmd=deposit&err=_yes');
				exit();
			}
		}
		else 
		{
			if (empty($ilance->GPC['deposit_amount']) OR $ilance->GPC['deposit_amount'] < $ilconfig['invoicesystem_mindepositamount'] OR $ilance->GPC['deposit_amount'] > $ilconfig['invoicesystem_maxdepositamount'])
			{ 
				refresh($ilpage['accounting'] . '?cmd=deposit&err=_yes');
				exit();
			}
		}
		$show['widescreen'] = false;
		$area_title = '{_deposit_funds_preview}';
		$page_title = SITE_NAME . ' - {_deposit_funds_preview}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[accounting]"] = '{_accounting}';
		$navcrumb["$ilpage[accounting]?cmd=deposit"] = '{_deposit_funds}';
		$navcrumb[""] = '{_preview}';
		$transaction_fee = 0;
		$account_id = $ilance->GPC['account_id'];
		$hidden = '';

		($apihook = $ilance->api('accounting_depositpreview_start')) ? eval($apihook) : false;

		// #### PAYPAL #########################################
		if ($ilance->GPC['account_id'] == 'paypal' AND isset($ilance->GPC['ppemail']))
		{
			// regular paypal
			$show['use_transaction_fees'] = false;
			$payment_method = '{_paypal}';
			if ($ilconfig['paypal_transaction_fee'] > 0 OR $ilconfig['paypal_transaction_fee2'] > 0)
			{
				$transaction_fee = ($ilconfig['paypal_transaction_fee'] * 100) . '% + ' . $ilance->currency->format($ilconfig['paypal_transaction_fee2']);
				$show['use_transaction_fees'] = true;
			}
		}
		// #### PAYPAL VIA ECHECK ##############################
		if ($ilance->GPC['account_id'] == 'paypalecheck' AND isset($ilance->GPC['ppemail']))
		{
			$show['use_transaction_fees'] = false;
			$payment_method = '{_paypal_echeck}';
			if ($ilconfig['paypal_deposit_echeck_active'])
			{
				if ($ilconfig['paypal_deposit_echeck_fee'] > 0)
				{
					// e-check fixed fees only
					$transaction_fee = $ilance->currency->format($ilconfig['paypal_deposit_echeck_fee']);
					$show['use_transaction_fees'] = true;
				}
			}
		}
		// #### CASHU ##########################################
		if ($ilance->GPC['account_id'] == 'cashu')
		{
			$show['use_transaction_fees'] = false;
			$payment_method = '{_cashu}';
			if ($ilconfig['cashu_transaction_fee'] > 0 OR $ilconfig['cashu_transaction_fee2'] > 0)
			{
				$transaction_fee = ($ilconfig['cashu_transaction_fee'] * 100) . '% + ' . $ilance->currency->format($ilconfig['cashu_transaction_fee2']);
				$show['use_transaction_fees'] = true;
			}
		}
		// #### MONEYBOOKERS ###################################
		if ($ilance->GPC['account_id'] == 'moneybookers')
		{
			$show['use_transaction_fees'] = false;
			$payment_method = '{_moneybookers}';
			if ($ilconfig['moneybookers_transaction_fee'] > 0 OR $ilconfig['moneybookers_transaction_fee2'] > 0)
			{
				$transaction_fee = ($ilconfig['moneybookers_transaction_fee'] * 100) . '% + ' . $ilance->currency->format($ilconfig['moneybookers_transaction_fee2']);
				$show['use_transaction_fees'] = true;
			}
		}
		// #### CREDIT CARD USER WILL SUPPLY ###################
		if ($ilconfig['use_internal_gateway'] != 'none' AND !empty($ilance->GPC['account_id']) AND $ilance->GPC['account_id'] == 'ccform')
		{
			// new credit card
			$transaction_fee = 0;
			$show['use_transaction_fees'] = false;
			$payment_method = '{_credit_card}';
			if ($ilconfig['use_internal_gateway'] == 'paypal_pro')
			{
				if ($ilconfig['cc_transaction_fee'] > 0 OR $ilconfig['cc_transaction_fee2'] > 0)
				{
					$transaction_fee = ($ilconfig['paypal_pro_transaction_fee'] * 100) . '% + ' . $ilance->currency->format($ilconfig['paypal_pro_transaction_fee2']);
					$show['use_transaction_fees'] = true;
				}
			}
			else 
			{
				if ($ilconfig['cc_transaction_fee'] > 0 OR $ilconfig['cc_transaction_fee2'] > 0)
				{
					$transaction_fee = ($ilconfig['cc_transaction_fee'] * 100) . '% + ' . $ilance->currency->format($ilconfig['cc_transaction_fee2']);
					$show['use_transaction_fees'] = true;
				}
			}
			$creditcard_number = isset($ilance->GPC['creditcard_number']) ? strip_tags($ilance->GPC['creditcard_number']) : '';
			$creditcard_type = isset($ilance->GPC['creditcard_type']) ? strip_tags($ilance->GPC['creditcard_type']) : '';
			$creditcard_cvv2 = isset($ilance->GPC['creditcard_cvv2']) ? intval($ilance->GPC['creditcard_cvv2']) : '';
			$creditcard_month = isset($ilance->GPC['creditcard_month']) ? strip_tags($ilance->GPC['creditcard_month']) : '';
			$creditcard_year = isset($ilance->GPC['creditcard_year']) ? intval($ilance->GPC['creditcard_year']) : '';
			$creditcard_firstname = isset($ilance->GPC['creditcard_firstname']) ? strip_tags($ilance->GPC['creditcard_firstname']) : '';
			$creditcard_lastname = isset($ilance->GPC['creditcard_lastname']) ? strip_tags($ilance->GPC['creditcard_lastname']) : '';
			$creditcard_name = isset($ilance->GPC['creditcard_name']) ? strip_tags($ilance->GPC['creditcard_name']) : $creditcard_firstname . ' ' . $creditcard_lastname;
			$creditcard_billing = isset($ilance->GPC['creditcard_billing']) ? strip_tags($ilance->GPC['creditcard_billing']) : '';
			$creditcard_postal = isset($ilance->GPC['creditcard_postal']) ? strip_tags($ilance->GPC['creditcard_postal']) : '';
			if (empty($creditcard_type) OR empty($creditcard_cvv2) OR empty($creditcard_number) OR empty($creditcard_month) OR empty($creditcard_year) OR empty($creditcard_name) OR empty($creditcard_billing) OR empty($creditcard_postal))
			{
				refresh($ilpage['accounting'] . '?cmd=deposit&err=_yes');
				exit();
			}
		}
		// #### OFFLINE DEPOSIT METHOD ########################
		if (substr($ilance->GPC['account_id'], 0, 7) == 'offline')
		{
			$payment_method = '';
			$sql = $ilance->db->query("
				SELECT name, fee
				FROM " . DB_PREFIX . "deposit_offline_methods 
				WHERE id = '" . substr($ilance->GPC['account_id'], 8) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$payment_method = $res['name'];
					$deposit_fee = sprintf("%01.2f", $res['fee']);
				}
			}
			$hidden = '<input type="hidden" name="transactionidx" value="' . $ilance->accounting_payment->construct_transaction_id() . '" />';
		}
		// #### CREDIT CARD SAVED IN DB ########################
		if ($ilconfig['use_internal_gateway'] != 'none' AND !empty($ilance->GPC['account_id']) AND is_numeric($ilance->GPC['account_id']) AND $ilance->GPC['account_id'] > 0)
		{
			// existing credit card
			$transaction_fee = 0;
			$show['use_transaction_fees'] = false;
			$payment_method = '{_credit_card}';
			if ($ilconfig['cc_transaction_fee'] > 0 OR $ilconfig['cc_transaction_fee2'] > 0)
			{
				$transaction_fee = ($ilconfig['cc_transaction_fee'] * 100) . '% + ' . $ilance->currency->format($ilconfig['cc_transaction_fee2']);
				$show['use_transaction_fees'] = true;
			}
		}
		$ppemail = isset($ilance->GPC['ppemail']) ? $ilance->GPC['ppemail'] : '';
		$deposit_amount = sprintf("%01.2f", $ilance->GPC['deposit_amount2']);
		$deposit_amount_formatted = $ilance->currency->format($deposit_amount);
		$credit_amount = sprintf("%01.2f", $ilance->GPC['deposit_amount']);
		$credit_amount_formatted = $ilance->currency->format($credit_amount);
		$returnurl = isset($ilance->GPC['returnurl']) ? urlencode($ilance->GPC['returnurl']) : '';
		$pprint_array = array('returnurl','hidden','creditcard_type','creditcard_cvv2','creditcard_number','creditcard_month','creditcard_year','creditcard_name','creditcard_billing','creditcard_postal','ppemail','credit_amount','credit_amount_formatted','deposit_amount_formatted','transaction_fee_formatted','account_id','deposit_amount','ip','referer','transaction_fee_notice','account_number','transaction_fee','payment_method','deposit_amount');

		($apihook = $ilance->api('accounting_depositpreview_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_deposit_preview.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### DEPOSIT FUNDS HANDLER ##################################################
	else if ($ilance->GPC['cmd'] == '_deposit-funds' AND isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'submit' AND !empty($ilance->GPC['account_id']) AND !empty($ilance->GPC['deposit_amount']))
	{
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$sel_member_results = $ilance->db->fetch_array($sql, DB_ASSOC);

			($apihook = $ilance->api('accounting_depositsubmit_start')) ? eval($apihook) : false;

			// #### PAYPAL #################################
			if ($ilance->GPC['account_id'] == 'paypal')
			{
				$area_title = '{_deposit_funds_via_paypal}';
				$page_title = SITE_NAME . ' - {_deposit_funds_via_paypal}';
				list($ppcurrency, $ilance->GPC['deposit_amount']) = $ilance->accounting->check_currency('paypal', $ilconfig['globalserverlocale_defaultcurrency'], $ilance->GPC['deposit_amount']);
				list($ppcurrency, $ilance->GPC['credit_amount']) = $ilance->accounting->check_currency('paypal', $ilconfig['globalserverlocale_defaultcurrency'], $ilance->GPC['credit_amount']);
				// DEPOSIT|USERID|INVOICEID|CREDIT_AMOUNT|INVOICETYPE|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID
				$customencrypted = 'DEPOSIT|' . $sel_member_results['user_id'] . '|0|' . sprintf("%01.2f", $ilance->GPC['credit_amount']) . '|0|0|0|0|0';
				$address = ($ilconfig['paypal_sandbox'] == '1') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
				$hidden_form_start = '<form action="https://' . $address . '/cgi-bin/webscr" method="post" accept-charset="UTF-8" style="margin:0px">
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="' . $ilconfig['paypal_business_email'] . '" />
	<input type="hidden" name="payer_email" value="' . $ilance->GPC['ppemail'] . '" />
	<input type="hidden" name="return" value="' . HTTP_SERVER . $ilpage['accounting'] . '?cmd=main&msg=_deposit-complete" />
	<input type="hidden" name="custom" value="' . $customencrypted . '" />
	<input type="hidden" name="undefined_quantity" value="0" />
	<input type="hidden" name="item_name" value="{_deposit_funds_via_paypal}" />
	<input type="hidden" name="amount" value="' . sprintf("%01.2f", $ilance->GPC['deposit_amount']) . '" />
	<input type="hidden" name="currency_code" value="' . $ppcurrency . '" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="cancel_return" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />
	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="notify_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_paypal" />';
				$hidden_form_end = '</form>';
				$ilance->template->fetch('main', 'accounting_deposit_paypal.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', array('hidden_form_start','hidden_form_end','pp_fee_amount_formatted','we_will','make_dep','pp_fee_amount','paypal','moment','y_will','fund_y','fyi','now_makes','paypal_hidden_form','account_id','deposit_amount','ip','referer','transaction_fee_notice','account_number','transaction_fee','payment_method','deposit_amount'));
				exit();
			}
			// #### PAYPAL VIA ECHECK ######################
			else if ($ilance->GPC['account_id'] == 'paypalecheck')
			{
				$area_title = '{_deposit_funds_via_paypal}';
				$page_title = SITE_NAME . ' - {_deposit_funds_via_paypal}';
				list($ppcurrency, $ilance->GPC['deposit_amount']) = $ilance->accounting->check_currency('paypal', $ilconfig['globalserverlocale_defaultcurrency'], $ilance->GPC['deposit_amount']);
				list($ppcurrency, $ilance->GPC['credit_amount']) = $ilance->accounting->check_currency('paypal', $ilconfig['globalserverlocale_defaultcurrency'], $ilance->GPC['credit_amount']);
				// DEPOSIT|USERID|INVOICEID|CREDIT_AMOUNT|INVOICETYPE|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID
				$customencrypted = 'DEPOSIT|' . $sel_member_results['user_id'] . '|0|' . sprintf("%01.2f", $ilance->GPC['credit_amount']) . '|0|0|0|0|0';
				$address = ($ilconfig['paypal_sandbox'] == '1') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
				$hidden_form_start = '<form action="https://' . $address . '/cgi-bin/webscr" method="post" accept-charset="UTF-8" style="margin:0px">
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="' . $ilconfig['paypal_business_email'] . '" />
	<input type="hidden" name="payer_email" value="' . $ilance->GPC['ppemail'] . '" />
	<input type="hidden" name="return" value="' . HTTP_SERVER . $ilpage['accounting'] . '?cmd=main&msg=_deposit-complete" />
	<input type="hidden" name="custom" value="' . $customencrypted . '" />
	<input type="hidden" name="undefined_quantity" value="0" />
	<input type="hidden" name="item_name" value="{_deposit_funds_via_paypal} ECheck" />
	<input type="hidden" name="amount" value="' . sprintf("%01.2f", $ilance->GPC['deposit_amount']) . '" />
	<input type="hidden" name="currency_code" value="' . $ppcurrency . '" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="cancel_return" value="' . HTTP_SERVER . $ilpage['accounting'] . '?cmd=main&msg=_deposit-cancelled" />
	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="notify_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_paypal" />';
				if (isset($ilance->GPC['account_id']) AND $ilance->GPC['account_id'] == 'paypalecheck')
				{
					// tell paypal we're using e-check
					$hidden_form_start .= '<input type="hidden" name="payment_type" value="echeck" />';	
				}
				else 
				{
					// tell paypal this payment type is instant
					$hidden_form_start .= '<input type="hidden" name="payment_type" value="instant" />';		
				}
				$hidden_form_end = '</form>';
				$ilance->template->fetch('main', 'deposit_paypal.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', array('pp_fee_amount_formatted','we_will','make_dep','pp_fee_amount','paypal','hidden_form_start','hidden_form_end','account_id','deposit_amount','ip','referer','transaction_fee_notice','account_number','transaction_fee','payment_method','deposit_amount'));
				exit();
			}
			// #### CASHU ##################################
			else if ($ilance->GPC['account_id'] == 'cashu')
			{
				$area_title = '{_fund_your_account_via_cashu}';
				$page_title = SITE_NAME . ' - {_fund_your_account_via_cashu}';
				list($ppcurrency, $ilance->GPC['deposit_amount']) = $ilance->accounting->check_currency('cashu', $ilconfig['globalserverlocale_defaultcurrency'], $ilance->GPC['deposit_amount']);
				list($ppcurrency, $ilance->GPC['credit_amount']) = $ilance->accounting->check_currency('cashu', $ilconfig['globalserverlocale_defaultcurrency'], $ilance->GPC['credit_amount']);
				// DEPOSIT|USERID|INVOICEID|CREDIT_AMOUNT|INVOICETYPE|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID
				$customencrypted = 'DEPOSIT|' . $sel_member_results['user_id'] . '|0|' . sprintf("%01.2f", $ilance->GPC['credit_amount']) . '|0|0|0|0|0';
				$hidden_form_start = $ilance->cashu->print_payment_form($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['email'], $ilance->GPC['deposit_amount'], 0, 0, '{_deposit_funds}', $ilconfig['cashu_business_email'], $ppcurrency, $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
				$hidden_form_end = '</form>';
				$ilance->template->fetch('main', 'accounting_deposit_cashu.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', array('pp_fee_amount_formatted','we_will','make_dep','pp_fee_amount','paypal','moment','y_will','fund_y','fyi','now_makes','hidden_form_start','hidden_form_end','account_id','deposit_amount','ip','referer','transaction_fee_notice','account_number','transaction_fee','payment_method','deposit_amount'));
				exit();
			}
			// #### MONEYBOOKERS ###########################
			else if ($ilance->GPC['account_id'] == 'moneybookers')
			{
				$area_title = '{_fund_your_account_balance_with_moneybookers}';
				$page_title = SITE_NAME . ' - {_fund_your_account_balance_with_moneybookers}';
				list($ppcurrency, $ilance->GPC['deposit_amount']) = $ilance->accounting->check_currency('moneybookers', $ilconfig['globalserverlocale_defaultcurrency'], $ilance->GPC['deposit_amount']);
				list($ppcurrency, $ilance->GPC['credit_amount']) = $ilance->accounting->check_currency('moneybookers', $ilconfig['globalserverlocale_defaultcurrency'], $ilance->GPC['credit_amount']);
				// DEPOSIT|USERID|INVOICEID|CREDIT_AMOUNT|INVOICETYPE|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID
				$customencrypted = 'DEPOSIT|' . $sel_member_results['user_id'] . '|0|' . sprintf("%01.2f", $ilance->GPC['credit_amount']) . '|0|0|0|0|0';
				$hidden_form_start = $ilance->moneybookers->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $ilance->GPC['deposit_amount'], 0, 0, '{_deposit_funds}', $ilconfig['moneybookers_business_email'], $ppcurrency, $ilconfig['moneybookers_secret_code'], $customencrypted, 0);
				$hidden_form_end = '</form>';
				$ilance->template->fetch('main', 'accounting_deposit_moneybookers.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', array('pp_fee_amount_formatted','we_will','make_dep','pp_fee_amount','paypal','hidden_form_start','hidden_form_end','account_id','deposit_amount','ip','referer','transaction_fee_notice','account_number','transaction_fee','payment_method','deposit_amount'));
				exit();
			}
			// #### PROCESS USER-SUBMITTED CARD ############
			else if ($ilance->GPC['account_id'] == 'ccform')
			{
				$custom = array();
				$custom['creditcard_type'] = strip_tags($ilance->GPC['creditcard_type']);
				$custom['creditcard_number'] = strip_tags($ilance->GPC['creditcard_number']);
				$custom['creditcard_firstname'] = isset($ilance->GPC['creditcard_firstname']) ? strip_tags($ilance->GPC['creditcard_firstname']) : '';
				$custom['creditcard_lastname'] = isset($ilance->GPC['creditcard_firstname']) ? strip_tags($ilance->GPC['creditcard_lastname']) : '';
				$custom['creditcard_name'] = strip_tags($ilance->GPC['creditcard_name']);
				$custom['creditcard_billing'] = strip_tags($ilance->GPC['creditcard_billing']);
				$custom['creditcard_postal'] = strip_tags($ilance->GPC['creditcard_postal']);
				$custom['creditcard_cvv2'] = strip_tags($ilance->GPC['creditcard_cvv2']);
				$custom['creditcard_month'] = strip_tags($ilance->GPC['creditcard_month']);
				$custom['creditcard_year'] = strip_tags($ilance->GPC['creditcard_year']);
				$response = $ilance->accounting_creditcard->process_creditcard_deposit($ilance->GPC['account_id'], $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['deposit_amount'], $ilance->GPC['credit_amount'], $custom);
				if ($response == '1')
				{
					$area_title = '{_account_deposit_successful}';
					$page_title = SITE_NAME . ' - {_account_deposit_successful}';
					print_notice('{_deposit_transaction_complete}', '{_your_deposit_transaction_is_complete}' . "<br /><br />" . '{_please_contact_customer_support}', $ilpage['accounting'], '{_my_account}');
					exit();    
				}
				else if ($response == '2')
				{
					print_notice('{_credit_card_not_authenticated}', '{_sorry_it_appears_this_credit_card_has_not_been_authenticated}', $ilpage['accounting'], '{_my_account}');
					exit();                    
				}
				else
				{
					$transaction_message = $ilance->paymentgateway->get_response_message();
					$error_code = isset($ilance->paymentgateway->error['number']) ? $ilance->paymentgateway->error['number'] : '';
					$date_time = DATETIME24H;
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('creditcard_processing_error');		
					$ilance->email->set(array(
						'{{gatewayresponse}}' => $ilance->paymentgateway->get_answer(),
						'{{gatewaymessage}}' => $ilance->paymentgateway->get_response_message(),
						'{{gatewayerrorcode}}' => $ilance->paymentgateway->error['number'],
						'{{ipaddress}}' =>IPADDRESS,
						'{{location}}' => LOCATION,
						'{{scripturi}}' => SCRIPT_URI,
						'{{gateway}}' => '{_' . $ilconfig['use_internal_gateway'] . '}',
						'{{member}}' => $_SESSION['ilancedata']['user']['username'],
						'{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
					));
					$ilance->email->send();
					$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', array('error_code', 'date_time','transaction_message','transaction_code'));
					exit();    
				}
			}
			// #### PLATNOSCI #################################
			else if ($ilance->GPC['account_id'] == 'platnosci')
			{
				$area_title = '{_platnosci}';
				$page_title = SITE_NAME . ' - {_platnosci}';
				// DEPOSIT|USERID|INVOICEID|CREDIT_AMOUNT|INVOICETYPE|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID
				$customencrypted = 'DEPOSIT|' . $sel_member_results['user_id'] . '|0|' . sprintf("%01.2f", $ilance->GPC['deposit_amount']) . '|0|0|0|0|0';
				$hidden_form_start = $ilance->platnosci->print_direct_payment_form($ilance->GPC['deposit_amount'], $ilance->GPC['deposit_amount'], '', $customencrypted);
				$hidden_form_end = '</form>';
				$ilance->template->fetch('main', 'accounting_deposit_platnosci.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', array('hidden_form_start','hidden_form_end','pp_fee_amount_formatted','we_will','make_dep','pp_fee_amount','paypal','moment','y_will','fund_y','fyi','now_makes','paypal_hidden_form','account_id','deposit_amount','ip','referer','transaction_fee_notice','account_number','transaction_fee','payment_method','deposit_amount'));
				exit();
			}
			// #### DEPOSIT OFFLINE METHODS ###################
			else if (substr($ilance->GPC['account_id'], 0, 7) == 'offline')
			{
				$name = $number = $swift = '';
				$sql = $ilance->db->query("
					SELECT name, number, swift, company_name, company_address, custom_notes
					FROM " . DB_PREFIX . "deposit_offline_methods 
					WHERE id = '" . substr($ilance->GPC['account_id'], 8) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$name = $row['name'];
						$number = $row['number'];
						$swift = $row['swift'];
						$companyname = $row['company_name'];
						$company_address = $row['company_address'];
						$custom_notes = $row['custom_notes'];
					}
				}
				$area_title = $name;
				$page_title = SITE_NAME . $name;
				$companyname = (!empty($companyname)) ? $companyname : $ilconfig['globalserversettings_companyname'];
				$company_address = (!empty($company_address)) ? $company_address : $ilconfig['globalserversettings_siteaddress'];
				$show['custom_notes'] = !empty($custom_notes) ? true : false;
				$amount = isset($ilance->GPC['deposit_amount']) ? $ilance->currency->format($ilance->GPC['deposit_amount']) : 0;
				$credit = isset($ilance->GPC['credit_amount']) ? $ilance->currency->format($ilance->GPC['credit_amount']) : 0;
				$username = fetch_user('username', $_SESSION['ilancedata']['user']['userid']);
				$transactionidx = isset($ilance->GPC['transactionidx']) ? $ilance->GPC['transactionidx'] : $ilance->accounting_payment->construct_transaction_id(); ;
				$sql = $ilance->db->query("
					SELECT invoiceid 
					FROM " . DB_PREFIX . "invoices
					WHERE transactionid = '" . $transactionidx . "'
				");
				if($ilance->db->num_rows($sql) == 0)
				{
					$invoice_id = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						$_SESSION['ilancedata']['user']['userid'],
						0,
						0,
						0,
						'{_deposit_funds_to_your_account}',
						sprintf("%01.2f", $ilance->GPC['deposit_amount']),
						'',
						'unpaid',
						'credit',
						'bank',
						DATETIME24H,
						DATETIME24H,
						DATETIME24H,
						'',
						0,
						0,
						1,
						$transactionidx,
						1,
						0
					);
					$existing = array(
						'{{gateway}}' => '{_offline_payment}',
						'{{username}}' => $username,
						'{{amount}}' => $amount,
						'{{paymethod}}' => $name,					  
						'{{invoiceid}}' => $transactionidx,
						'{{cost}}' => $credit,
						'{{ip}}' => IPADDRESS,
					);
					$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
					$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
					$ilance->email->get('member_deposit_funds_offline_payment');		
					$ilance->email->set($existing);
					$ilance->email->send();
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('member_deposit_funds_offline_payment_admin');		
					$ilance->email->set($existing);
					$ilance->email->send();
				}
				$ilance->template->fetch('main', 'accounting_deposit_offline.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', array('custom_notes','transactionidx','username','amount','company_address','companyname','name','number','swift','hidden_form_start','hidden_form_end','pp_fee_amount_formatted','we_will','make_dep','pp_fee_amount','paypal','moment','y_will','fund_y','fyi','now_makes','paypal_hidden_form','account_id','deposit_amount','ip','referer','transaction_fee_notice','account_number','transaction_fee','payment_method','deposit_amount'));
				exit();
			}
			// #### PROCESS SAVED CREDIT CARD ##############
			else if ($ilance->GPC['account_id'] > 0)
			{
				$response = $ilance->accounting_creditcard->process_creditcard_deposit($ilance->GPC['account_id'], $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['deposit_amount'], $ilance->GPC['credit_amount'], array());
				if ($response == '1')
				{
					$area_title = '{_account_deposit_successful}';
					$page_title = SITE_NAME . ' - {_account_deposit_successful}';
					print_notice('{_deposit_transaction_complete}', '{_your_deposit_transaction_is_complete}' . "<br /><br />" . '{_please_contact_customer_support}', $ilpage['accounting'], '{_my_account}');
					exit();    
				}
				else if ($response == '2')
				{
					print_notice('{_credit_card_not_authenticated}', '{_sorry_it_appears_this_credit_card_has_not_been_authenticated}', $ilpage['accounting'], '{_my_account}');
					exit();                    
				}
				else
				{
					$transaction_message = $ilance->paymentgateway->get_response_message();
					$date_time = DATETIME24H;
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('creditcard_processing_error');		
					$ilance->email->set(array(
						'{{gatewayresponse}}' => $ilance->paymentgateway->get_answer(),
						'{{gatewaymessage}}' => $ilance->paymentgateway->get_response_message(),
						'{{ipaddress}}' =>IPADDRESS,
						'{{location}}' => LOCATION,
						'{{scripturi}}' => SCRIPT_URI,
						'{{gateway}}' => $ilconfig['use_internal_gateway'],
						'{{member}}' => $_SESSION['ilancedata']['user']['username'],
						'{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
					));
					$ilance->email->send();
					$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', array('date_time','transaction_message','transaction_code'));
					exit();    
				}
			}

			($apihook = $ilance->api('accounting_depositsubmit_end')) ? eval($apihook) : false;                
		}
	}
	// #### WITHDRAW FUNDS MENU ####################################################
	else if ($ilance->GPC['cmd'] == 'withdraw')
	{
		$topnavlink = array(
			'withdraw'
		);
		$show['leftnav'] = true;
		if (empty($_SESSION['ilancedata']['user']['active']) OR $_SESSION['ilancedata']['user']['active'] == 'no' OR $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'withdraw') == 'no')
		{
			$area_title = '{_access_denied_to_withdraw_funds}';
			$page_title = SITE_NAME . ' - {_access_denied_to_withdraw_funds}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . ' <a href="' . HTTPS_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('withdraw'));
			exit();
		}
		$area_title = '{_withdraw_funds_menu}';
		$page_title = SITE_NAME . ' - {_withdraw_funds_menu}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[accounting]"] = '{_accounting}';
		$navcrumb[""] = '{_withdraw_funds}';

		($apihook = $ilance->api('accounting_withdraw_start')) ? eval($apihook) : false;

		// #### ERRORS #####################################################
		$show['withdraw_errors'] = false;
		$show['withdraw_nofunds_error'] = false;
		$show['withdraw_badamount_error'] = false;
		if (isset($ilance->GPC['err']) AND $ilance->GPC['err'] == '_yes')
		{
			$show['withdraw_errors'] = true;
		}
		if (isset($ilance->GPC['err']) AND $ilance->GPC['err'] == '_no-funds')
		{
			$show['withdraw_nofunds_error'] = true;
		}
		if (isset($ilance->GPC['err']) AND $ilance->GPC['err'] == '_inv-amount')
		{
			$show['withdraw_badamount_error'] = true;
		}
		$headinclude .= '
	<script type="text/javascript">
	<!--
	function validate_withdraw_form(f)
	{
	haveerrors = 0;
	(f.withdraw_amount.value.length < 1) ? showImage("withdraw_amounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("withdraw_amounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
	return (!haveerrors);
	}
	//-->
	</script>
	';
		$accountdata = $ilance->accounting->fetch_user_balance($_SESSION['ilancedata']['user']['userid']);
		$available_balance = $ilance->currency->format($accountdata['available_balance']);
		$min_withdraw_amount = $ilance->currency->format($ilconfig['invoicesystem_minwithdrawamount']);
		$max_withdraw_amount = $ilance->currency->format($ilconfig['invoicesystem_maxwithdrawamount']);
		$withdraw_method_pulldown = $ilance->accounting_print->print_paymethod_pulldown('withdraw', 'account_id', $_SESSION['ilancedata']['user']['userid'], $javascript = '');
		$pprint_array = array('withdraw_method_pulldown','available_balance','max_withdraw_amount','min_withdraw_amount','withdraw_amount_formatted','account_id','transaction_fee_notice','withdraw_errors');

		($apihook = $ilance->api('accounting_withdraw_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_withdraw.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### WITHDRAW FUNDS PREVIEW #################################################
	else if ($ilance->GPC['cmd'] == '_secure-withdraw' AND isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'preview')
	{
		$area_title = '{_withdraw_funds_preview}';
		$page_title = SITE_NAME . ' - {_withdraw_funds_preview}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[accounting]"] = '{_accounting}';
		$navcrumb["$ilpage[accounting]?cmd=withdraw"] = '{_withdraw_funds}';
		$navcrumb[""] = '{_preview}';

		($apihook = $ilance->api('accounting_withdrawpreview_start')) ? eval($apihook) : false;

		$show['paypal'] = 0;
		if (empty($ilance->GPC['withdraw_amount']) OR $ilance->GPC['withdraw_amount'] < $ilconfig['invoicesystem_minwithdrawamount'] OR $ilance->GPC['withdraw_amount'] > $ilconfig['invoicesystem_maxwithdrawamount'])
		{ 
			refresh($ilpage['accounting'].'?cmd=withdraw&err=_inv-amount');
			exit();
		} 
		else if (empty($ilance->GPC['account_id']))
		{ 
			refresh($ilpage['accounting'].'?cmd=withdraw&err=_yes');
			exit();
		} 
		$accountdata = $ilance->accounting->fetch_user_balance($_SESSION['ilancedata']['user']['userid']);
		$account_number = $accountdata['account_number'];
		$available_balance = $accountdata['available_balance'];
		$total_balance = $accountdata['total_balance'];
		$seladdressinfo = $ilance->db->query("
			SELECT address, address2, city, state, zip_code
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($seladdressinfo) > 0)
		{
			$resultaddressinfo = $ilance->db->fetch_array($seladdressinfo);		
			$address = mb_strtoupper($resultaddressinfo['address']) . "&nbsp;" . mb_strtoupper($resultaddressinfo['address2']) . "<br />" . mb_strtoupper($resultaddressinfo['city']) . ",&nbsp;" . mb_strtoupper($resultaddressinfo['state']) . "&nbsp;-&nbsp;" . mb_strtoupper($resultaddressinfo['zip_code']);
		}
		if (isset($ilance->GPC['withdraw_amount']) AND $ilance->GPC['withdraw_amount'] > $available_balance)
		{
			refresh($ilpage['accounting'] . '?cmd=withdraw&err=_no-funds');
			exit();
		}
		else if (isset($ilance->GPC['withdraw_amount']) AND $ilance->GPC['withdraw_amount'] < 0 OR $ilance->GPC['withdraw_amount'] < $ilconfig['invoicesystem_minwithdrawamount'] OR $ilance->GPC['withdraw_amount'] > $ilconfig['invoicesystem_maxwithdrawamount'])
		{
			refresh($ilpage['accounting'] . '?cmd=withdraw&err=_inv-amount');
			exit();
		}
		else
		{
			$account_id = $ilance->GPC['account_id'];
			// #### CHECK / MONEY ORDER WITHDRAW ###########
			if ($ilance->GPC['account_id'] == 'check')
			{
				$payment_method = $address;
				if ($ilconfig['check_withdraw_fee_active'])
				{
					$show['use_withdraw_fees'] = true;
					$withdraw_amount = $ilance->GPC['withdraw_amount'];
					$withdraw_amount_total = ($ilance->GPC['withdraw_amount'] + $ilconfig['check_withdraw_fee']);
					$transaction_amount = $ilconfig['check_withdraw_fee'];
					$withdraw_amount_request = $ilance->currency->format($ilance->GPC['withdraw_amount']);
					$transaction_fee_formatted = $ilance->currency->format($ilconfig['check_withdraw_fee']);
					$withdraw_amount_formatted = $ilance->currency->format($ilance->GPC['withdraw_amount'] + $ilconfig['check_withdraw_fee']);
					$withdraw_debit_amount = $withdraw_amount_total;
				}
				else
				{
					$show['use_withdraw_fees'] = false;
					$withdraw_amount = $ilance->GPC['withdraw_amount'];
					$withdraw_amount_request = $ilance->currency->format($withdraw_amount);
					$withdraw_amount_formatted = $ilance->currency->format($ilance->GPC['withdraw_amount']);
					$withdraw_debit_amount = $withdraw_amount;
				}
			}
			// #### PAYPAL WITHDRAW ########################
			else if ($ilance->GPC['account_id'] == 'paypal')
			{
				$show['paypal'] = 1;
				$payment_method = 'Paypal';
				if ($ilconfig['paypal_withdraw_fee_active'])
				{
					$show['use_withdraw_fees'] = true;
					$withdraw_amount = $ilance->GPC['withdraw_amount'];
					$withdraw_amount_total = ($ilance->GPC['withdraw_amount'] + $ilconfig['paypal_withdraw_fee']);
					$transaction_amount = $ilconfig['paypal_withdraw_fee'];
					$withdraw_amount_request = $ilance->currency->format($ilance->GPC['withdraw_amount']);
					$transaction_fee_formatted = $ilance->currency->format($ilconfig['paypal_withdraw_fee']);
					$withdraw_amount_formatted = $ilance->currency->format($ilance->GPC['withdraw_amount'] + $ilconfig['paypal_withdraw_fee']);
					$withdraw_debit_amount = $withdraw_amount_total;
				}
				else
				{
					$show['use_withdraw_fees'] = false;
					$withdraw_amount = $ilance->GPC['withdraw_amount'];                        
					$withdraw_amount_request = $ilance->currency->format($withdraw_amount);
					$withdraw_amount_formatted = $ilance->currency->format($ilance->GPC['withdraw_amount']);
					$withdraw_debit_amount = $withdraw_amount;
				}
			}
			// #### WIRE TRANSFER WITHDRAW #################
			else
			{
				$bankinfo = '{_bank}: ' . ucwords(handle_input_keywords($ilance->db->fetch_field(DB_PREFIX . "bankaccounts", "beneficiary_account_number = '" . intval($account_id) . "'", "beneficiary_bank_name")));
				$bankinfo .= ' #' . str_repeat('X', (mb_strlen(intval($account_id)) - 4)) . mb_substr(intval($account_id), -4, 4);
				$bankinfo .= ' (' . ucwords(handle_input_keywords($ilance->db->fetch_field(DB_PREFIX . "bankaccounts", "beneficiary_account_number = '" . intval($account_id) . "'", "bank_account_type"))) . ')';
				$payment_method = '{_wire_transfer}<div class="smaller" style="padding-top:3px">' . $bankinfo . '</div>';
				if ($ilconfig['bank_withdraw_fee_active'])
				{
					$show['use_withdraw_fees'] = true;
					$withdraw_amount = $ilance->GPC['withdraw_amount'];
					$withdraw_amount_total = ($ilance->GPC['withdraw_amount'] + $ilconfig['bank_withdraw_fee']);
					$transaction_amount = $ilconfig['bank_withdraw_fee'];
					$withdraw_amount_request = $ilance->currency->format($ilance->GPC['withdraw_amount']);
					$transaction_fee_formatted = $ilance->currency->format($ilconfig['bank_withdraw_fee']);
					$withdraw_amount_formatted = $ilance->currency->format($ilance->GPC['withdraw_amount'] + $ilconfig['bank_withdraw_fee']);
					$withdraw_debit_amount = $withdraw_amount_total;
				}
				else
				{
					$show['use_withdraw_fees'] = true;
					$withdraw_amount = $ilance->GPC['withdraw_amount'];
					$withdraw_amount_request = $ilance->currency->format($withdraw_amount);
					$withdraw_amount_formatted = $ilance->currency->format($ilance->GPC['withdraw_amount']);
					$withdraw_debit_amount = $withdraw_amount;
				}
			}
			// ensure the withdraw and any fees can be covered by the user's account balance
			if (isset($withdraw_debit_amount) AND $withdraw_debit_amount > $available_balance)
			{
				refresh($ilpage['accounting'] . '?cmd=withdraw&err=_no-funds');
				exit();
			}
			$pprint_array = array('withdraw_debit_amount','transaction_amount','withdraw_amount_request','transaction_fee_notice','transaction_fee_formatted','withdraw_amount_formatted','account_id','withdraw_amount','ip','referer','account_number','payment_method');

			($apihook = $ilance->api('accounting_withdrawpreview_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'accounting_withdraw_preview.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	// #### WITHDRAW FUNDS HANDLER #################################################
	else if ($ilance->GPC['cmd'] == '_withdraw-funds' AND isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'submit' AND !empty($ilance->GPC['account_id']) AND !empty($ilance->GPC['withdraw_amount']))
	{
		($apihook = $ilance->api('accounting_withdrawsubmit_start')) ? eval($apihook) : false;

		$accountdata = $ilance->accounting->fetch_user_balance($_SESSION['ilancedata']['user']['userid']);
		// #### CHECK / MONEY ORDER ####################################
		if ($ilance->GPC['account_id'] == 'check')
		{
			($apihook = $ilance->api('accounting_withdrawsubmit_check_start')) ? eval($apihook) : false;

			$withdraw_fee = 0;
			if ($ilconfig['check_withdraw_fee_active'] AND $ilconfig['check_withdraw_fee'] > 0)
			{
				$withdraw_amount = $ilance->GPC['withdraw_amount'];
				$withdraw_fee = $ilconfig['check_withdraw_fee'];
				$withdraw_final = ($withdraw_amount + $withdraw_fee);
				$sch_id = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_online_account_withdraw_via_check}',
					sprintf("%01.2f", $withdraw_amount),
					'',
					'scheduled',
					'debit',
					'check',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					'{_online_account_withdraw_via_check} Fee: (' . $ilance->currency->format($withdraw_fee) . ' * charged separately)',
					0,
					0,
					1,
					'',
					0,
					1
				);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $withdraw_amount) . "
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$withdrawinvoiceid = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_check_withdraw_transaction_fee}',
					sprintf("%01.2f", $withdraw_fee),
					sprintf("%01.2f", $withdraw_fee),
					'paid',
					'debit',
					'account',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					'{_check_withdraw_transaction_fee}: (RE: TXN #' . $sch_id . ') - {_online_account_withdraw_via_check}',
					0,
					0,
					1,
					'',
					0,
					1
				);
				// update account balance minus any withdraw fees
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $withdraw_fee) . ",
					total_balance = total_balance - " . sprintf("%01.2f", $withdraw_fee) . "
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// update the original withdraw transaction with the withdrawinvoiceid field
				// to show any withdraw fees within admincp withdraw manager
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET withdrawinvoiceid = '" . intval($withdrawinvoiceid) . "'
					WHERE invoiceid = '" . intval($sch_id) . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET iswithdrawfee = '1',
					parentid = '" . intval($sch_id) . "'
					WHERE invoiceid = '" . intval($withdrawinvoiceid) . "'
				", 0, null, __FILE__, __LINE__);
			}
			else
			{
				$ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_online_account_withdraw_via_check}',
					sprintf("%01.2f", $ilance->GPC['withdraw_amount']),
					'',
					'scheduled',
					'debit',
					'check',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					'{_online_account_withdraw_via_check}',
					0,
					0,
					0,
					'',
					0,
					1
				);
				// update user's available balance
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $ilance->GPC['withdraw_amount']) . "
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			}
			$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('member_check_withdraw_confirmation');		
			$ilance->email->set(array(
				'{{username}}' => $_SESSION['ilancedata']['user']['username'],
				'{{withdraw_amount}}' => $ilance->currency->format($ilance->GPC['withdraw_amount']),
				'{{withdraw_fee}}' => $ilance->currency->format($withdraw_fee),
				'{{withdraw_total}}' => $ilance->currency->format($ilance->GPC['withdraw_debit_amount']),
			));
			$ilance->email->send();
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('member_check_withdraw_confirmation_admin');		
			$ilance->email->set(array(
				'{{username}}' => $_SESSION['ilancedata']['user']['username'],
				'{{withdraw_amount}}' => $ilance->currency->format($ilance->GPC['withdraw_amount']),
				'{{withdraw_fee}}' => $ilance->currency->format($withdraw_fee),
				'{{withdraw_total}}' => $ilance->currency->format($ilance->GPC['withdraw_debit_amount']),
			));
			$ilance->email->send();
			$area_title = '{_withdraw_request_complete}';
			$page_title = SITE_NAME . ' - {_withdraw_request_complete}';

			($apihook = $ilance->api('accounting_withdrawsubmit_check_end')) ? eval($apihook) : false;

			refresh(HTTPS_SERVER . $ilpage['accounting'] . '?note=withdraw-pending');
			exit();
		}
		// #### PAYPAL #################################################
		else if ($ilance->GPC['account_id'] == 'paypal')
		{
			($apihook = $ilance->api('accounting_withdrawsubmit_paypal_start')) ? eval($apihook) : false;

			$withdraw_fee = 0;

			// does admin set fees for paypal withdraws?
			if ($ilconfig['paypal_withdraw_fee_active'] AND $ilconfig['paypal_withdraw_fee'] > 0)
			{
				$withdraw_amount = $ilance->GPC['withdraw_amount'];
				$withdraw_fee = $ilconfig['paypal_withdraw_fee'];
				$withdraw_final = ($withdraw_amount + $withdraw_fee);
				$sch_id = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_online_account_withdraw_via_paypal}',
					sprintf("%01.2f", $withdraw_amount),
					'',
					'scheduled',
					'debit',
					'paypal',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					$ilance->GPC['custom'], // paypal address specified by the user withdrawing funds
					0,
					0,
					1,
					'',
					0,
					1
				);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $withdraw_amount) . "
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$withdrawinvoiceid = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_paypal_withdraw_transaction_fee}',
					sprintf("%01.2f", $withdraw_fee),
					sprintf("%01.2f", $withdraw_fee),
					'paid',
					'debit',
					'account',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					$ilance->GPC['custom'],
					0,
					0,
					1,
					'',
					0,
					1
				);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $withdraw_fee) . ",
					total_balance = total_balance - " . sprintf("%01.2f", $withdraw_fee) . "
					WHERE user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// update the original withdraw transaction with the withdrawinvoiceid field
				// to show any withdraw fees within admincp withdraw manager
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET withdrawinvoiceid = '" . intval($withdrawinvoiceid) . "'
					WHERE invoiceid = '" . intval($sch_id) . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET iswithdrawfee = '1',
					parentid = '" . intval($sch_id) . "'
					WHERE invoiceid = '" . intval($withdrawinvoiceid) . "'
				", 0, null, __FILE__, __LINE__);
			}
			else
			{
				// no paypal withdraw fees active
				$ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_online_account_withdraw_via_paypal}',
					sprintf("%01.2f", $ilance->GPC['withdraw_amount']),
					'',
					'scheduled',
					'debit',
					'paypal',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					$ilance->GPC['custom'],
					0,
					0,
					0,
					'',
					0,
					1
				);
				// update user's available balance
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $ilance->GPC['withdraw_amount']) . "
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			}
			// #### DISPATCH EMAIL #########################
			$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('paypal_withdraw_confirmation');		
			$ilance->email->set(array(
				'{{username}}' => $_SESSION['ilancedata']['user']['username'],
				'{{withdraw_amount}}' => $ilance->currency->format($ilance->GPC['withdraw_amount']),
				'{{withdraw_fee}}' => $ilance->currency->format($withdraw_fee),
				'{{withdraw_total}}' => $ilance->currency->format($ilance->GPC['withdraw_amount'] + $withdraw_fee),
			));
			$ilance->email->send();
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('paypal_withdraw_confirmation_admin');		
			$ilance->email->set(array(
				'{{username}}' => $_SESSION['ilancedata']['user']['username'],
				'{{withdraw_amount}}' => $ilance->currency->format($ilance->GPC['withdraw_amount']),
				'{{withdraw_fee}}' => $ilance->currency->format($withdraw_fee),
				'{{withdraw_total}}' => $ilance->currency->format($ilance->GPC['withdraw_amount'] + $withdraw_fee),
			));
			$ilance->email->send();
			$area_title = '{_withdraw_request_complete}';
			$page_title = SITE_NAME . ' - {_withdraw_request_complete}';

			($apihook = $ilance->api('accounting_withdrawsubmit_paypal_end')) ? eval($apihook) : false;

			refresh(HTTPS_SERVER . $ilpage['accounting'] . '?note=withdraw-pending');
			exit();
		}
		// #### WIRE TRANSFER ##########################################
		else
		{
			($apihook = $ilance->api('accounting_withdrawsubmit_wire_start')) ? eval($apihook) : false;

			$withdraw_fee = 0;
			if ($ilconfig['bank_withdraw_fee_active'] AND $ilconfig['bank_withdraw_fee'] > 0)
			{
				$withdraw_amount = $ilance->GPC['withdraw_amount'];
				$withdraw_fee = $ilconfig['bank_withdraw_fee'];
				$withdraw_final = ($withdraw_amount + $withdraw_fee);
				$sch_id = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_online_account_withdraw_via_wire_transfer}',
					sprintf("%01.2f", $withdraw_amount),
					'',
					'scheduled',
					'debit',
					'bank',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					'{_online_account_withdraw_via_wire_transfer} - {_amount}: ' . $ilance->currency->format($withdraw_amount) . ', {_fee}: ' . $ilance->currency->format($withdraw_fee) . ' * charged separately',
					0,
					0,
					1,
					'',
					0,
					1
				);
				// update account balance minus any scheduled transactions
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $withdraw_amount) . "
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// create transaction for withdraw fees
				$withdrawinvoiceid = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_wire_withdraw_transaction_fee}',
					sprintf("%01.2f", $withdraw_fee),
					sprintf("%01.2f", $withdraw_fee),
					'paid',
					'debit',
					'account',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					'{_wire_withdraw_transaction_fee}: (RE: TXN #' . $sch_id . ') - {_online_account_withdraw_via_wire_transfer}',
					0,
					0,
					1,
					'',
					0,
					1
				);
				// update account balance minus any withdraw fees
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $withdraw_fee) . ",
					total_balance = total_balance - " . sprintf("%01.2f", $withdraw_fee) . "
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// update the original withdraw transaction with the withdrawinvoiceid field
				// to show any withdraw fees within admincp withdraw manager
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET withdrawinvoiceid = '" . $withdrawinvoiceid . "'
					WHERE invoiceid = '" . intval($sch_id) . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET iswithdrawfee = '1',
					parentid = '" . intval($sch_id) . "'
					WHERE invoiceid = '" . intval($withdrawinvoiceid) . "'
				", 0, null, __FILE__, __LINE__);
			}
			else
			{
				// no transaction fees for bank wire transfer
				$ilance->accounting->insert_transaction(
					0,
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					'{_online_account_withdraw_via_wire_transfer}',
					sprintf("%01.2f", $ilance->GPC['withdraw_amount']),
					'',
					'scheduled',
					'debit',
					'bank',
					DATETIME24H,
					DATETIME24H,
					DATETIME24H,
					'{_online_account_withdraw_via_wire_transfer}',
					0,
					0,
					0,
					'',
					0,
					1
				);
				// update user's available balance
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = available_balance - " . sprintf("%01.2f", $ilance->GPC['withdraw_amount']) . "
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			}
			$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('member_wire_withdraw_confirmation');		
			$ilance->email->set(array(
				'{{username}}' => $_SESSION['ilancedata']['user']['username'],
				'{{withdraw_amount}}' => $ilance->currency->format($ilance->GPC['withdraw_amount']),
				'{{withdraw_fee}}' => $ilance->currency->format($withdraw_fee),
				'{{withdraw_total}}' => $ilance->currency->format($ilance->GPC['withdraw_amount'] + $withdraw_fee),
			));
			$ilance->email->send();
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('member_wire_withdraw_confirmation_admin');		
			$ilance->email->set(array(
				'{{username}}' => $_SESSION['ilancedata']['user']['username'],
				'{{withdraw_amount}}' => $ilance->currency->format($ilance->GPC['withdraw_amount']),
				'{{withdraw_fee}}' => $ilance->currency->format($withdraw_fee),
				'{{withdraw_total}}' => $ilance->currency->format($ilance->GPC['withdraw_amount'] + $withdraw_fee),
			));
			$ilance->email->send();
			$area_title = '{_withdraw_request_complete}';
			$page_title = SITE_NAME . ' - {_withdraw_request_complete}';

			($apihook = $ilance->api('accounting_withdrawsubmit_wire_end')) ? eval($apihook) : false;

			refresh(HTTPS_SERVER . $ilpage['accounting'] . '?note=withdraw-pending');
			exit();
		}
	}
	// #### ADD NEW CREDIT CARD ####################################################
	else if ($ilance->GPC['cmd'] == '_add-creditcard')
	{
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addcreditcard') != 'yes')
		{
			$area_title = '{_access_denied_to_add_credit_card}';
			$page_title = SITE_NAME . ' - {_access_denied_to_add_credit_card}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addcreditcard'));
			exit();    
		}
		if ($ilconfig['save_credit_cards'] == 0)
		{
			$area_title = '{_access_denied_to_add_credit_card}';
			$page_title = SITE_NAME . ' - {_access_denied_to_add_credit_card}';
			print_notice('{_access_denied}', '{_this_option_is_currently_not_available}', $ilpage['main'], ucwords('{_click_here}'));
			exit();         
		}

		($apihook = $ilance->api('accounting_addcreditcard_start')) ? eval($apihook) : false;

		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'checkpassword' AND isset($ilance->GPC['password']) AND !empty($ilance->GPC['password']))
		{
			verify_password_prompt($ilance->GPC['password'], $ilance->GPC['returnurl'], 'cardadd', 'new', 120);
		}
		$sql = $ilance->db->query("
			SELECT cc_id
			FROM " . DB_PREFIX . "creditcards
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		$ccs = $ilance->db->num_rows($sql);
		if ($ilconfig['multi_creditcard_support'] == 0 AND $ccs > 0)
		{
			$area_title = '{_one_credit_card_per_account_warning}';
			$page_title = SITE_NAME . ' - {_one_credit_card_per_account_warning}';
			print_notice('{_one_credit_card_per_account}', '{_were_sorry_we_only_allow_one_credit_card_per_account}' . "<br /><br />" . '{_please_contact_customer_support}', $ilpage['accounting'], '{_my_account}');
			exit();
		}
		$form = array();
		$form['errors'] = '';
		// #### PREVIEW MODE ###############################################
		if (isset($ilance->GPC['preview']))
		{
			$area_title = '{_credit_card_submit_preview}';
			$page_title = SITE_NAME . ' - {_credit_card_submit_preview}';
			// #### ERROR MESSAGES #############################################
			$form['mod10'] = $ilance->accounting->verify_creditcard_mod10($ilance->GPC['form']['number']);
			if ($form['mod10'] == 0)
			{
				$form['errors'] .= '{_card_number_does_not_appear_to_be_valid}';
			}
			if (empty($ilance->GPC['form']['number']))
			{
				$form['errors'] .= '{_card_number_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['cvv2']))
			{
				$form['errors'] .= '{_card_cvv_number_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['first_name']))
			{
				$form['errors'] .= '{_first_name_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['last_name']))
			{
				$form['errors'] .= '{_last_name_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['phone']))
			{
				$form['errors'] .= '{_phone_number_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['address1']))
			{
				$form['errors'] .= '{_billing_address_1_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['city']))
			{
				$form['errors'] .= '{_city_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['state']))
			{
				$form['errors'] .= '{_state_or_province_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['postalzip']))
			{
				$form['errors'] .= '{_zip_or_postal_code_cannot_be_empty}';
			}
			if ($ilconfig['advanced_email_filter'])
			{
				if ($ilance->security->verify_email_mx($ilance->GPC['form']['email'], 2) != 0)
				{
					$form['errors'] .= '{_email_address_does_not_appear_to_be_working}';
				}
			}
			if (!empty($form['errors']))
			{
				$form['errors'] = '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td><div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_red_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div style="padding:6px"><span style="float:left; padding-right:10px; padding-left:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" id="error" /></span>' . $form['errors'] . '</div><div class="s"><div class="e"><div class="w"></div></div></div></div></div></td></tr></table><div style="padding-bottom:12px"></div>';
			}
			else
			{
				$form['errors'] = '';
			}
			$form['number'] = trim($ilance->GPC['form']['number']);
			$form['cc_type_pulldown'] = $ilance->accounting->creditcard_type_pulldown($ilance->GPC['form']['type'], 'form[type]');
			$form['exp_mon_pulldown'] = $ilance->accounting->creditcard_month_pulldown($ilance->GPC['form']['expmon'], 'form[expmon]');
			$form['exp_year_pulldown'] = $ilance->accounting->creditcard_year_pulldown($ilance->GPC['form']['expyear'], 'form[expyear]');
			$form['cvv2'] = $ilance->GPC['form']['cvv2'];
			$form['first_name'] = $ilance->GPC['form']['first_name'];
			$form['last_name'] = $ilance->GPC['form']['last_name'];
			$form['phone'] = $ilance->GPC['form']['phone'];
			$form['email'] = $ilance->GPC['form']['email'];
			$form['address1'] = $ilance->GPC['form']['address1'];
			$form['address2'] = $ilance->GPC['form']['address2'];
			$form['city'] = $ilance->GPC['form']['city'];
			$form['state'] = $ilance->GPC['form']['state'];
			$form['postalzip'] = trim($ilance->GPC['form']['postalzip']);
			$form['cc_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown($ilance->GPC['form']['countryid'], $_SESSION['ilancedata']['user']['slng'], 'form[countryid]');
		}
		// ### SUBMIT MODE #################################################
		else if (isset($ilance->GPC['submit']))
		{
			$area_title = '{_submitting_new_credit_card}';
			$page_title = SITE_NAME . ' - {_submitting_new_credit_card}';
			if ($ilance->accounting->insert_creditcard($ilance->GPC['form'], $_SESSION['ilancedata']['user']['userid']))
			{
				print_notice('{_new_credit_card_was_added}', '{_you_have_successfully_supplied_your_account_with_a_new_credit_card}', $ilpage['accounting'], '{_my_account}');
				exit();
			}
		}
		// #### LANDING MODE ###############################################
		else
		{
			if (empty($_SESSION['ilancedata']['user']['cardadd_new']) OR !empty($_SESSION['ilancedata']['user']['cardadd_new']) AND $_SESSION['ilancedata']['user']['cardadd_new'] < TIMESTAMPNOW)
			{
				$html = '<div>{_this_area_requires_you_reenter_password}</div><div style="padding-top:14px"><form name="password" action="' . HTTPS_SERVER . $ilpage['accounting'] . '" method="post"><input type="hidden" name="cmd" value="_add-creditcard" /><input type="hidden" name="do" value="checkpassword" /><input type="hidden" name="returnurl" value="' . urlencode(PAGEURL) . '" /><table><tr><td>{_password}:</td><td><input type="password" name="password" value="" style="font-family: verdana" /> <input type="submit" value="{_submit}" class="buttons" /></td></tr></table></form></div>';
				print_notice('{_account_password_verification}', $html, $ilpage['accounting'], '{_back}');
				exit();
			}
			$form['number'] = '';
			$form['cvv2'] = '';
			$form['cc_type_pulldown'] = $ilance->accounting->creditcard_type_pulldown('', 'form[type]');
			$form['exp_mon_pulldown'] = $ilance->accounting->creditcard_month_pulldown('', 'form[expmon]');
			$form['exp_year_pulldown'] = $ilance->accounting->creditcard_year_pulldown('', 'form[expyear]');
			$form['cc_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown($_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng'], 'form[countryid]');
			$sql = $ilance->db->query("
				SELECT email, phone, first_name, last_name, address, address2, city, state, zip_code
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$form['address1'] = stripslashes($res['address']);
				$form['address2'] = stripslashes($res['address2']);
				$form['email'] = $res['email'];
				$form['phone'] = $res['phone'];
				$form['city'] = stripslashes(ucfirst($res['city']));
				$form['state'] = stripslashes(ucfirst($res['state']));
				$form['postalzip'] = trim(stripslashes(mb_strtoupper($res['zip_code'])));
				$form['first_name'] = stripslashes($res['first_name']);
				$form['last_name'] = stripslashes($res['last_name']);                                        
			}    
		}
		$pprint_array = array('login_include');

		($apihook = $ilance->api('accounting_addcreditcard_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_add_creditcard.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('main', array('form' => $form));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### UPDATE CREDIT CARD MENU ################################################
	else if ($ilance->GPC['cmd'] == '_update-creditcard')
	{
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addcreditcard') != 'yes')
		{
			$area_title = '{_access_denied_to_add_credit_card}';
			$page_title = SITE_NAME . ' - {_access_denied_to_add_credit_card}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addcreditcard'));
			exit();    
		}
		$area_title = '{_update_credit_card_menu}';
		$page_title = SITE_NAME . ' - {_update_credit_card_menu}';

		($apihook = $ilance->api('accounting_updatecreditcard_start')) ? eval($apihook) : false;

		$form = array();
		if (isset($ilance->GPC['cc_id']))
		{ // our cc_id coming from encrypted link in accounting menu
			$form['cc_id'] = intval($ilance->GPC['cc_id']);
		}
		else if (isset($ilance->GPC['form']['cc_id']))
		{
			$form['cc_id'] = intval($ilance->GPC['form']['cc_id']);
		}
		else
		{
			refresh($ilpage['accounting']);
			exit();
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'checkpassword' AND isset($ilance->GPC['password']) AND !empty($ilance->GPC['password']))
		{
			verify_password_prompt($ilance->GPC['password'], $ilance->GPC['returnurl'], 'cardupdate', $form['cc_id'], 120);
		}
		$form['errors'] = '';
		// #### PREVIEW MODE ###############################################
		if (isset($ilance->GPC['preview']))
		{
			$area_title = '{_credit_card_submit_preview}';
			$page_title = SITE_NAME . ' - {_credit_card_submit_preview}';
			// #### ERROR MESSAGES #############################################
			$form['mod10'] = $ilance->accounting->verify_creditcard_mod10($ilance->GPC['form']['number']);
			if ($form['mod10'] == 0)
			{
				$form['errors'] .= '{_card_number_does_not_appear_to_be_valid}';
			}
			if (empty($ilance->GPC['form']['number']))
			{
				$form['errors'] .= '{_card_number_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['cvv2']))
			{
				$form['errors'] .= '{_card_cvv_number_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['first_name']))
			{
				$form['errors'] .= '{_first_name_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['last_name']))
			{
				$form['errors'] .= '{_last_name_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['phone']))
			{
				$form['errors'] .= '{_phone_number_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['address1']))
			{
				$form['errors'] .= '{_billing_address_1_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['city']))
			{
				$form['errors'] .= '{_city_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['state']))
			{
				$form['errors'] .= '{_state_or_province_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['postalzip']))
			{
				$form['errors'] .= '{_zip_or_postal_code_cannot_be_empty}';
			}
			if ($ilconfig['advanced_email_filter'])
			{
				if ($ilance->security->verify_email_mx($ilance->GPC['form']['email'], 2))
				{
					$form['errors'] .= '{_email_address_does_not_appear_to_be_working}';
				}
			}
			if (!empty($form['errors']))
			{
				$form['errors'] = '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td><div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_red_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div style="padding:6px"><span style="float:left; padding-right:10px; padding-left:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" id="error" /></span>' . $form['errors'] . '</div><div class="s"><div class="e"><div class="w"></div></div></div></div></div></td></tr></table><div style="padding-bottom:12px"></div>';
			}
			else
			{
				$form['errors'] = '';
			}
			$form['cc_id'] = intval($ilance->GPC['form']['cc_id']);
			$form['number'] = trim($ilance->GPC['form']['number']);
			$form['cc_type_pulldown'] = $ilance->accounting->creditcard_type_pulldown($ilance->GPC['form']['type'], 'form[type]');
			$form['exp_mon_pulldown'] = $ilance->accounting->creditcard_month_pulldown($ilance->GPC['form']['expmon'], 'form[expmon]');
			$form['exp_year_pulldown'] = $ilance->accounting->creditcard_year_pulldown($ilance->GPC['form']['expyear'], 'form[expyear]');
			$form['cvv2'] = $ilance->GPC['form']['cvv2'];
			$form['first_name'] = $ilance->GPC['form']['first_name'];
			$form['last_name'] = $ilance->GPC['form']['last_name'];
			$form['phone'] = $ilance->GPC['form']['phone'];
			$form['email'] = $ilance->GPC['form']['email'];
			$form['address1'] = $ilance->GPC['form']['address1'];
			$form['address2'] = $ilance->GPC['form']['address2'];
			$form['city'] = $ilance->GPC['form']['city'];
			$form['state'] = $ilance->GPC['form']['state'];
			$form['postalzip'] = trim($ilance->GPC['form']['postalzip']);
			$form['cc_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown($ilance->GPC['form']['countryid'], $_SESSION['ilancedata']['user']['slng'], 'form[countryid]');
		}
		// #### SUBMIT MODE ################################################
		else if (isset($ilance->GPC['submit']))
		{
			$area_title = '{_submitting_new_credit_card}';
			$page_title = SITE_NAME . ' - {_submitting_new_credit_card}';

			($apihook = $ilance->api('accounting_updatecreditcardsubmit_start')) ? eval($apihook) : false;

			if ($ilance->accounting->update_creditcard($ilance->GPC['form'], $_SESSION['ilancedata']['user']['userid']))
			{
				($apihook = $ilance->api('accounting_updatecreditcardsubmit_end')) ? eval($apihook) : false;

				print_notice('{_existing_credit_card_information_updated}', '{_you_have_successfully_updated_your_credit_card}', $ilpage['accounting'], '{_return_to_the_previous_menu}');
				exit();
			}
		}

		// #### LANDING MODE ###############################################
		else
		{
			// check for cookie so we don't need to re-enter password
			if (empty($_SESSION['ilancedata']['user']['cardupdate_' . $form['cc_id']]) OR !empty($_SESSION['ilancedata']['user']['cardupdate_' . $form['cc_id']]) AND $_SESSION['ilancedata']['user']['cardupdate_' . $form['cc_id']] < TIMESTAMPNOW)
			{
				$html = '<div>{_this_area_requires_you_reenter_password}</div><div style="padding-top:14px"><form name="password" action="' . HTTPS_SERVER . $ilpage['accounting'] . '" method="post"><input type="hidden" name="crypted" value="' . $ilance->GPC['crypted'] . '" /><input type="hidden" name="do" value="checkpassword" /><input type="hidden" name="returnurl" value="' . urlencode(PAGEURL) . '" /><table><tr><td>{_password}:</td><td><input type="password" name="password" value="" style="font-family: verdana" /> <input type="submit" value="{_submit}" class="buttons" /></td></tr></table></form></div>';
				print_notice('{_account_password_verification}', $html, $ilpage['accounting'], '{_back}');
				exit();
			}
			$area_title = '{_update_credit_card_menu}';
			$page_title = SITE_NAME . ' - {_update_credit_card_menu}';
			$sql = $ilance->db->query("
				SELECT cc_id, date_added, date_updated, user_id, creditcard_number, creditcard_expiry, cvv2, name_on_card, phone_of_cardowner, email_of_cardowner, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country, creditcard_status, default_card, creditcard_type, authorized, auth_amount1, auth_amount2, attempt_num, trans1_id, trans2_id
				FROM " . DB_PREFIX . "creditcards
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND cc_id = '" . intval($ilance->GPC['cc_id']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);

				($apihook = $ilance->api('accounting_updatecreditcardlanding_start')) ? eval($apihook) : false;

				$form['cc_id'] = $ilance->GPC['cc_id'];
				$form['number'] = $ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);               
				$form['number_hidden'] = substr_replace($form['number'], 'XX XXXX XXXX ', 2 , (mb_strlen($form['number']) - 6));                
				$form['cc_type_pulldown'] = $ilance->accounting->creditcard_type_pulldown($res['creditcard_type'], 'form[type]');
				$form['cvv2'] = $res['cvv2'];
				if (!empty($res['creditcard_expiry']))
				{
					$split = preg_split('//', $res['creditcard_expiry'], -1, PREG_SPLIT_NO_EMPTY);
					$form['exp_mon_pulldown'] = $ilance->accounting->creditcard_month_pulldown($split[0] . $split[1], 'form[expmon]');
					$form['exp_year_pulldown'] = $ilance->accounting->creditcard_year_pulldown($split[2] . $split[3], 'form[expyear]');
					unset($split);
				}
				else
				{
					$form['exp_mon_pulldown'] = $ilance->accounting->creditcard_month_pulldown(1, 'form[expmon]');
					$form['exp_year_pulldown'] = $ilance->accounting->creditcard_year_pulldown(date('Y'), 'form[expyear]');
				}
				$split = $res['name_on_card'];
				$split = explode(' ', $split);                
				$form['first_name'] = $split[0];
				$form['last_name'] = $split[1];
				unset($split);
				$form['phone'] = $res['phone_of_cardowner'];
				$form['email'] = $res['email_of_cardowner'];
				$form['address1'] = $res['card_billing_address1'];
				$form['address2'] = $res['card_billing_address2'];
				$form['city'] = $res['card_city'];
				$form['state'] = $res['card_state'];
				$form['postalzip'] = $res['card_postalzip'];
				$form['cc_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown($res['card_country'], $_SESSION['ilancedata']['user']['slng'], 'form[countryid]');

				($apihook = $ilance->api('accounting_updatecreditcardlanding_end')) ? eval($apihook) : false;
			}	        
		}
		$pprint_array = array('cc_id');

		($apihook = $ilance->api('accounting_updatecreditcard_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_update_creditcard.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('main', array('form' => $form));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### REMOVE CREDIT CARD HANDLER #############################################
	else if ($ilance->GPC['cmd'] == '_remove-creditcard' AND isset($ilance->GPC['cc_id']))
	{
		$area_title = '{_removing_credit_card}';
		$page_title = SITE_NAME . ' - {_removing_credit_card}';

		($apihook = $ilance->api('accounting_removecreditcard_start')) ? eval($apihook) : false;

		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'checkpassword' AND isset($ilance->GPC['password']) AND !empty($ilance->GPC['password']))
		{
			verify_password_prompt($ilance->GPC['password'], $ilance->GPC['returnurl'], 'cardremove', $ilance->GPC['cc_id'], 120);
		}
		// check for cookie so we don't need to re-enter password
		if (empty($_SESSION['ilancedata']['user']['cardremove_' . $ilance->GPC['cc_id']]) OR !empty($_SESSION['ilancedata']['user']['cardremove_' . $ilance->GPC['cc_id']]) AND $_SESSION['ilancedata']['user']['cardremove_' . $ilance->GPC['cc_id']] < TIMESTAMPNOW)
		{
			$html = '<div>{_this_area_requires_you_reenter_password}</div><div style="padding-top:14px"><form name="password" action="' . HTTPS_SERVER . $ilpage['accounting'] . '" method="post"><input type="hidden" name="crypted" value="' . $ilance->GPC['crypted'] . '" /><input type="hidden" name="do" value="checkpassword" /><input type="hidden" name="returnurl" value="' . urlencode(PAGEURL) . '" /><table><tr><td>{_password}:</td><td><input type="password" name="password" value="" style="font-family: verdana" /> <input type="submit" value="{_submit}" class="buttons" /></td></tr></table></form></div>';
			print_notice('{_account_password_verification}', $html, $ilpage['accounting'], '{_back}');
			exit();
		}
		if ($ilance->accounting->remove_creditcard($ilance->GPC['cc_id'], $_SESSION['ilancedata']['user']['userid']))
		{
			($apihook = $ilance->api('accounting_removecreditcard_end')) ? eval($apihook) : false;

			print_notice('{_credit_card_removed}', '{_you_have_successfully_removed_your_credit_card}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['accounting'], '{_my_account}');
			exit();
		}
	}
	// #### ADD NEW BANK ACCOUNT ###################################################
	else if ($ilance->GPC['cmd'] == '_add-bankaccount')
	{
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addbankaccount') != 'yes')
		{
			$area_title = '{_access_denied_to_add_bank_account}';
			$page_title = SITE_NAME . ' - {_access_denied_to_add_bank_account}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addbankaccount'));
			exit();
		}

		($apihook = $ilance->api('accounting_addbankaccount_start')) ? eval($apihook) : false;

		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'checkpassword' AND isset($ilance->GPC['password']) AND !empty($ilance->GPC['password']))
		{
			verify_password_prompt($ilance->GPC['password'], $ilance->GPC['returnurl'], 'bankadd', 'new', 120);
		}
		$sql = $ilance->db->query("
			SELECT bank_id
			FROM " . DB_PREFIX . "bankaccounts
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		$count = $ilance->db->num_rows($sql);
		if ($ilconfig['multi_bankaccount_support'] == 0 AND $count > 0)
		{
			$area_title = '{_one_bank_per_account_warning}';
			$page_title = SITE_NAME . ' - {_one_bank_per_account_warning}';
			print_notice('{_one_bank_account_per_account}', '{_were_sorry_we_only_allow_one_customer_supplied_bank_account_per_account}', $ilpage['accounting'], '{_my_account}');
			exit();
		}
		$area_title = '{_add_new_bank_account}';
		$page_title = SITE_NAME . ' - {_add_new_bank_account_menu}';
		$form = array();
		$form['errors'] = '';
		// #### PREVIEW MODE ###############################################
		if (isset($ilance->GPC['preview']))
		{
			$area_title = '{_bank_account_submit_preview}';
			$page_title = SITE_NAME . ' - {_bank_account_submit_preview}';
			// #### ERROR MESSAGES #############################################
			if (empty($ilance->GPC['form']['beneficiary_account_name']))
			{
				$form['errors'] .= '{_beneficiary_account_name_cannot_be_empty}<br /> ';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_name']))
			{
				$form['errors'] .= '{_beneficiary_bank_name_cannot_be_empty}<br /> ';
			}
			if (empty($ilance->GPC['form']['beneficiary_account_number']))
			{
				$form['errors'] .= '{_beneficiary_account_number_cannot_be_empty}<br /> ';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_routing_number_swift']))
			{
				$form['errors'] .= '{_bank_routing_number_swift_code_cannot_be_empty}<br /> ';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_address_1']))
			{
				$form['errors'] .= '{_address_cannot_be_empty}<br /> ';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_city']))
			{
				$form['errors'] .= '{_city_cannot_be_empty}<br /> ';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_state']))
			{
				$form['errors'] .= '{_state_or_province_cannot_be_empty}<br /> ';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_zipcode']))
			{
				$form['errors'] .= '{_zip_or_postal_code_cannot_be_empty}<br /> ';
			}
			if (!empty($form['errors']))
			{
				$form['errors'] = '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td><div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_red_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div style="padding:6px"><span style="float:left; padding-right:10px; padding-left:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" id="error" /></span></div><div style="padding-left:45px">' . $form['errors'] . '</div><div class="s"><div class="e"><div class="w"></div></div></div></div></div></td></tr></table><div style="padding-bottom:12px"></div>';
			}
			else
			{
				$form['errors'] = '';
			}
			$form['beneficiary_account_name'] = $ilance->GPC['form']['beneficiary_account_name'];
			$form['beneficiary_bank_name'] = $ilance->GPC['form']['beneficiary_bank_name'];
			$form['beneficiary_account_number'] = $ilance->GPC['form']['beneficiary_account_number'];
			$form['beneficiary_bank_routing_number_swift'] = $ilance->GPC['form']['beneficiary_bank_routing_number_swift'];
			$form['beneficiary_bank_address_1'] = $ilance->GPC['form']['beneficiary_bank_address_1'];
			$form['beneficiary_bank_address_2'] = $ilance->GPC['form']['beneficiary_bank_address_2'];
			$form['beneficiary_bank_city'] = $ilance->GPC['form']['beneficiary_bank_city'];
			$form['beneficiary_bank_state'] = $ilance->GPC['form']['beneficiary_bank_state'];
			$form['beneficiary_bank_zipcode'] = trim($ilance->GPC['form']['beneficiary_bank_zipcode']);
			$form['bank_account_type_pulldown'] = $ilance->accounting->print_bank_account_type_pulldown($ilance->GPC['form']['bank_account_type'], 'form[bank_account_type]');
			$form['bank_currency_pulldown'] = $ilance->accounting->print_destination_currency_pulldown($ilance->GPC['form']['destination_currency_id'], 'form[destination_currency_id]');
			$form['bank_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown($ilance->GPC['form']['beneficiary_bank_country_id'], $_SESSION['ilancedata']['user']['slng'], 'form[beneficiary_bank_country_id]');
		}
		// ### SUBMIT MODE #################################################
		else if (isset($ilance->GPC['submit']))
		{
			$area_title = '{_submitting_new_bank_account}';
			$page_title = SITE_NAME . ' - {_submitting_new_bank_account}';
			if ($ilance->accounting->insert_bank_account($ilance->GPC['form'], $_SESSION['ilancedata']['user']['userid']))
			{
				print_notice('{_new_bank_account_was_added}', '{_you_have_successfully_supplied_your_account_with_a_new_bank_account}', $ilpage['accounting'], '{_my_account}');
				exit();
			}
		}
		// #### LANDING MODE ###############################################
		else
		{
			// check for cookie so we don't need to re-enter password
			if (empty($_SESSION['ilancedata']['user']['bankadd_new']) OR !empty($_SESSION['ilancedata']['user']['bankadd_new']) AND $_SESSION['ilancedata']['user']['bankadd_new'] < TIMESTAMPNOW)
			{
				$html = '<div>{_this_area_requires_you_reenter_password}</div><div style="padding-top:14px"><form name="password" action="' . HTTPS_SERVER . $ilpage['accounting'] . '" method="post"><input type="hidden" name="cmd" value="_add-bankaccount" /><input type="hidden" name="do" value="checkpassword" /><input type="hidden" name="returnurl" value="' . urlencode(PAGEURL) . '" /><table><tr><td>{_password}:</td><td><input type="password" name="password" value="" style="font-family: verdana" /> <input type="submit" value="{_submit}" class="buttons" /></td></tr></table></form></div>';
				print_notice('{_account_password_verification}', $html, $ilpage['accounting'], '{_back}');
				exit();
			}
			$form['beneficiary_account_name'] = '';
			$form['beneficiary_bank_name'] = '';
			$form['beneficiary_account_number'] = '';
			$form['beneficiary_bank_routing_number_swift'] = '';
			$form['beneficiary_bank_address_1'] = '';
			$form['beneficiary_bank_address_2'] = '';
			$form['beneficiary_bank_city'] = '';
			$form['beneficiary_bank_state'] = '';
			$form['beneficiary_bank_zipcode'] = '';
			$form['bank_account_type_pulldown'] = $ilance->accounting->print_bank_account_type_pulldown('', 'form[bank_account_type]');
			$form['bank_currency_pulldown'] = $ilance->accounting->print_destination_currency_pulldown('', 'form[destination_currency_id]');
			$form['bank_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown('', $_SESSION['ilancedata']['user']['slng'], 'form[beneficiary_bank_country_id]');
			// pre populate bank form with details we already know about this user
			$sql = $ilance->db->query("
				SELECT beneficiary_account_name, destination_currency_id, beneficiary_bank_name, beneficiary_account_number, beneficiary_bank_routing_number_swift, bank_account_type, beneficiary_bank_address_1, beneficiary_bank_address_2, beneficiary_bank_city, beneficiary_bank_state, beneficiary_bank_zipcode, beneficiary_bank_country_id
				FROM " . DB_PREFIX . "bankaccounts
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$form['beneficiary_account_name'] = $res['beneficiary_account_name'];
				$form['beneficiary_bank_name'] = $res['beneficiary_bank_name'];
				$form['beneficiary_account_number'] = $res['beneficiary_account_number'];
				$form['beneficiary_bank_routing_number_swift'] = $res['beneficiary_bank_routing_number_swift'];
				$form['beneficiary_bank_address_1'] = $res['beneficiary_bank_address_1'];
				$form['beneficiary_bank_address_2'] = $res['beneficiary_bank_address_2'];
				$form['beneficiary_bank_city'] = $res['beneficiary_bank_city'];
				$form['beneficiary_bank_state'] = $res['beneficiary_bank_state'];
				$form['beneficiary_bank_zipcode'] = $res['beneficiary_bank_zipcode'];
				$form['bank_account_type_pulldown'] = $ilance->accounting->print_bank_account_type_pulldown($res['bank_account_type'], 'form[bank_account_type]');
				$form['bank_currency_pulldown'] = $ilance->accounting->print_destination_currency_pulldown($res['destination_currency_id'], 'form[destination_currency_id]');
				$form['bank_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown($res['beneficiary_bank_country_id'], $_SESSION['ilancedata']['user']['slng'], 'form[beneficiary_bank_country_id]');
			}    
		}
		$pprint_array = array('beneb','new_ba_content','addb','benzip','bencit','bebank','beban','banka','bene','chec','savi','banro','benac','dest','bank','benba','benef','ba_country_pulldown','ba_currency_pulldown','bank_country_name_session','bank_routing_number_session','account_name_session','bank_name_session','account_number_session','bank_address1_session','bank_address2_session','bank_city_session','bank_zipcode_session','ip','referer');

		($apihook = $ilance->api('accounting_addbankaccount_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_add_bank_account.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('main', array('form' => $form));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### UPDATE BANK ACCOUNT ####################################################
	else if ($ilance->GPC['cmd'] == '_update-bankaccount' AND isset($ilance->GPC['bankid']))
	{
		$area_title = '{_update_bank_account_menu}';
		$page_title = SITE_NAME . ' - {_update_bank_account_menu}';
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addbankaccount') != 'yes')
		{
			$area_title = '{_access_denied_to_add_bank_account}';
			$page_title = SITE_NAME . ' - {_access_denied_to_add_bank_account}';

			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addbankaccount'));
			exit();
		}
		$area_title = '{_update_bank_account_menu}';
		$page_title = SITE_NAME . ' - {_update_bank_account_menu}';

		($apihook = $ilance->api('accounting_updatebankaccount_start')) ? eval($apihook) : false;

		$form = array();
		if (isset($ilance->GPC['bankid']))
		{ // our bankid coming from encrypted link in accounting menu
			$form['bankid'] = intval($ilance->GPC['bankid']);
			$ilance->GPC['form']['bankid'] = $form['bankid'];
		}
		else if (isset($ilance->GPC['form']['bankid']))
		{
			$form['bankid'] = intval($ilance->GPC['form']['bankid']);
		}
		else
		{
			refresh($ilpage['accounting']);
			exit();
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'checkpassword' AND isset($ilance->GPC['password']) AND !empty($ilance->GPC['password']))
		{
			verify_password_prompt($ilance->GPC['password'], $ilance->GPC['returnurl'], 'bankupdate', $form['bankid'], 120);
		}
		$form['errors'] = '';
		// #### PREVIEW MODE ###############################################
		if (isset($ilance->GPC['preview']))
		{
			$area_title = '{_bank_account_submit_preview}';
			$page_title = SITE_NAME . ' - {_bank_account_submit_preview}';
			// #### ERROR MESSAGES #############################################
			if (empty($ilance->GPC['form']['beneficiary_account_name']))
			{
				$form['errors'] .= '{_beneficiary_account_name_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_name']))
			{
				$form['errors'] .= '{_beneficiary_bank_name_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['beneficiary_account_number']))
			{
				$form['errors'] .= '{_beneficiary_account_number_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_routing_number_swift']))
			{
				$form['errors'] .= '{_bank_routing_number_swift_code_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_address_1']))
			{
				$form['errors'] .= '{_address_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_city']))
			{
				$form['errors'] .= '{_city_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_state']))
			{
				$form['errors'] .= '{_state_or_province_cannot_be_empty}';
			}
			if (empty($ilance->GPC['form']['beneficiary_bank_zipcode']))
			{
				$form['errors'] .= '{_zip_or_postal_code_cannot_be_empty}';
			}

			if (!empty($form['errors']))
			{
				$form['errors'] = '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td><div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_red_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div style="padding:6px"><span style="float:left; padding-right:10px; padding-left:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" id="error" /></span>' . $form['errors'] . '</div><div class="s"><div class="e"><div class="w"></div></div></div></div></div></td></tr></table><div style="padding-bottom:12px"></div>';
			}
			else
			{
				$form['errors'] = '';
			}
			$form['bankid'] = $ilance->GPC['form']['bankid'];
			$form['beneficiary_account_name'] = $ilance->GPC['form']['beneficiary_account_name'];
			$form['beneficiary_bank_name'] = $ilance->GPC['form']['beneficiary_bank_name'];
			$form['beneficiary_account_number'] = $ilance->GPC['form']['beneficiary_account_number'];
			$form['beneficiary_bank_routing_number_swift'] = $ilance->GPC['form']['beneficiary_bank_routing_number_swift'];
			$form['beneficiary_bank_address_1'] = $ilance->GPC['form']['beneficiary_bank_address_1'];
			$form['beneficiary_bank_address_2'] = $ilance->GPC['form']['beneficiary_bank_address_2'];
			$form['beneficiary_bank_city'] = $ilance->GPC['form']['beneficiary_bank_city'];
			$form['beneficiary_bank_state'] = $ilance->GPC['form']['beneficiary_bank_state'];
			$form['beneficiary_bank_zipcode'] = trim($ilance->GPC['form']['beneficiary_bank_zipcode']);
			$form['bank_account_type_pulldown'] = $ilance->accounting->print_bank_account_type_pulldown($ilance->GPC['form']['bank_account_type'], 'form[bank_account_type]');
			$form['bank_currency_pulldown'] = $ilance->accounting->print_destination_currency_pulldown($ilance->GPC['form']['destination_currency_id'], 'form[destination_currency_id]');
			$form['bank_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown($ilance->GPC['form']['beneficiary_bank_country_id'], $_SESSION['ilancedata']['user']['slng'], 'form[beneficiary_bank_country_id]');
		}
		// #### SUBMIT MODE ################################################
		else if (isset($ilance->GPC['submit']))
		{
			$area_title = '{_submitting_new_bank_account}';
			$page_title = SITE_NAME . ' - {_submitting_new_bank_account}';

			($apihook = $ilance->api('accounting_updatebankaccountsubmit_start')) ? eval($apihook) : false;

			if ($ilance->accounting->update_bank_account($ilance->GPC['form'], $_SESSION['ilancedata']['user']['userid']))
			{
				($apihook = $ilance->api('accounting_updatebankaccountsubmit_end')) ? eval($apihook) : false;

				print_notice('{_existing_bank_account_information_updated}', '{_you_have_successfully_updated_your_bank_account_details}', $ilpage['accounting'], '{_return_to_the_previous_menu}');
				exit();
			}
		}
		// #### LANDING MODE ###############################################
		else
		{
			// check for cookie so we don't need to re-enter password
			if (empty($_SESSION['ilancedata']['user']['bankupdate_' . $form['bankid']]) OR !empty($_SESSION['ilancedata']['user']['bankupdate_' . $form['bankid']]) AND $_SESSION['ilancedata']['user']['bankupdate_' . $form['bankid']] < TIMESTAMPNOW)
			{
				$html = '<div>{_this_area_requires_you_reenter_password}</div><div style="padding-top:14px"><form name="password" action="' . HTTPS_SERVER . $ilpage['accounting'] . '" method="post"><input type="hidden" name="crypted" value="' . $ilance->GPC['crypted'] . '" /><input type="hidden" name="do" value="checkpassword" /><input type="hidden" name="returnurl" value="' . urlencode(PAGEURL) . '" /><table><tr><td>{_password}:</td><td><input type="password" name="password" value="" style="font-family: verdana" /> <input type="submit" value="{_submit}" class="buttons" /></td></tr></table></form></div>';
				print_notice('{_account_password_verification}', $html, $ilpage['accounting'], '{_back}');
				exit();
			}
			$sql = $ilance->db->query("
				SELECT bank_id, user_id, beneficiary_account_name, destination_currency_id, beneficiary_bank_name, beneficiary_account_number, beneficiary_bank_routing_number_swift, bank_account_type, beneficiary_bank_address_1, beneficiary_bank_address_2, beneficiary_bank_city, beneficiary_bank_state, beneficiary_bank_zipcode, beneficiary_bank_country_id, wire_bin_type
				FROM " . DB_PREFIX . "bankaccounts
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND bank_id = '" . intval($ilance->GPC['bankid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);

				($apihook = $ilance->api('accounting_updatebankaccountlanding_start')) ? eval($apihook) : false;

				$form['bankid'] = $ilance->GPC['bankid'];
				$form['beneficiary_account_name'] = $res['beneficiary_account_name'];
				$form['beneficiary_bank_name'] = $res['beneficiary_bank_name'];
				$form['beneficiary_account_number'] = $res['beneficiary_account_number'];
				$form['beneficiary_bank_routing_number_swift'] = $res['beneficiary_bank_routing_number_swift'];
				$form['beneficiary_bank_address_1'] = $res['beneficiary_bank_address_1'];
				$form['beneficiary_bank_address_2'] = $res['beneficiary_bank_address_2'];
				$form['beneficiary_bank_city'] = $res['beneficiary_bank_city'];
				$form['beneficiary_bank_state'] = $res['beneficiary_bank_state'];
				$form['beneficiary_bank_zipcode'] = $res['beneficiary_bank_zipcode'];
				$form['bank_account_type_pulldown'] = $ilance->accounting->print_bank_account_type_pulldown($res['bank_account_type'], 'form[bank_account_type]');
				$form['bank_currency_pulldown'] = $ilance->accounting->print_destination_currency_pulldown($res['destination_currency_id'], 'form[destination_currency_id]');
				$form['bank_country_pulldown'] = $ilance->accounting->creditcard_country_pulldown($res['beneficiary_bank_country_id'], $_SESSION['ilancedata']['user']['slng'], 'form[beneficiary_bank_country_id]');

				($apihook = $ilance->api('accounting_updatebankaccountlanding_end')) ? eval($apihook) : false;
			}	        
		}

		$pprint_array = array('bankid');

		($apihook = $ilance->api('accounting_updatebankaccount_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'accounting_update_bank_account.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('main', array('form' => $form));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}    
	// #### REMOVE BANK ACCOUNT HANDLER ############################################
	else if ($ilance->GPC['cmd'] == '_remove-bankaccount' AND isset($ilance->GPC['bankid']))
	{
		$area_title = '{_removing_bank_account}';
		$page_title = SITE_NAME . ' - {_removing_bank_account}';

		($apihook = $ilance->api('accounting_removebankaccount_start')) ? eval($apihook) : false;

		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'checkpassword' AND isset($ilance->GPC['password']) AND !empty($ilance->GPC['password']))
		{
			verify_password_prompt($ilance->GPC['password'], $ilance->GPC['returnurl'], 'bankremove', $ilance->GPC['bankid'], 120);
		}
		// check for cookie so we don't need to re-enter password
		if (empty($_SESSION['ilancedata']['user']['bankremove_' . $ilance->GPC['bankid']]) OR !empty($_SESSION['ilancedata']['user']['bankremove_' . $ilance->GPC['bankid']]) AND $_SESSION['ilancedata']['user']['bankremove_' . $ilance->GPC['bankid']] < TIMESTAMPNOW)
		{
			$html = '<div>{_this_area_requires_you_reenter_password}</div><div style="padding-top:14px"><form name="password" action="' . HTTPS_SERVER . $ilpage['accounting'] . '" method="post"><input type="hidden" name="crypted" value="' . $ilance->GPC['crypted'] . '" /><input type="hidden" name="do" value="checkpassword" /><input type="hidden" name="returnurl" value="' . urlencode(PAGEURL) . '" /><table><tr><td>{_password}:</td><td><input type="password" name="password" value="" style="font-family: verdana" /> <input type="submit" value="{_submit}" class="buttons" /></td></tr></table></form></div>';
			print_notice('{_account_password_verification}', $html, $ilpage['accounting'], '{_back}');
			exit();
		}
		if ($ilance->accounting->remove_bank_account(intval($ilance->GPC['bankid']), $_SESSION['ilancedata']['user']['userid']))
		{
			($apihook = $ilance->api('accounting_removebankaccount_end')) ? eval($apihook) : false;

			print_notice('{_bank_account_removed}', '{_you_have_successfully_removed_your_bank_account_from_the_marketplace}', $ilpage['accounting'], '{_my_account}');
			exit();
		}
	}
	// #### CREDIT CARD AUTHENTICATION STEP 1 OF 2 #################################
	else if ($ilance->GPC['cmd'] == '_auth-creditcard' AND isset($ilance->GPC['cc_id']) AND $ilance->GPC['cc_id'] != "")
	{
		$area_title = '{_credit_card_authentication_process}';
		$page_title = SITE_NAME . ' - {_credit_card_authentication_process}';
		$cc_id = intval($ilance->GPC['cc_id']);
		$sql = $ilance->db->query("
			SELECT creditcard_number
			FROM " . DB_PREFIX . "creditcards
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND cc_id = '" . intval($ilance->GPC['cc_id']) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$decrypted = $ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
			$ccnum_hidden = substr_replace($decrypted, 'XX XXXX XXXX ', 2 , (mb_strlen($decrypted) - 6));
			$ilance->template->fetch('main', 'accounting_authorize_creditcard_start.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('cc_id','ip','referer','ccnum_hidden'));
			exit();        
		}
		else
		{
			refresh(HTTP_SERVER . $ilpage['accounting']);
			exit();
		}
	}
}
// #### ACCOUNTING CP LANDING PAGE #############################################
if (!isset($ilance->GPC['cmd']) OR isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'management')
{
	$show['widescreen'] = false;
	$area_title = '{_main_accounting_menu}';
	$page_title = SITE_NAME . ' - {_main_accounting_menu}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[""] = '{_accounting}';
	$show['leftnav'] = true;	
	// #### define top header nav ##########################
	$topnavlink = array('accounting');
		
	($apihook = $ilance->api('accounting_start')) ? eval($apihook) : false;
	
	$accountdata = $ilance->accounting->fetch_user_balance($_SESSION['ilancedata']['user']['userid']);
	$account_number = $accountdata['account_number'];
	$available_balance = $accountdata['available_balance'];
	$total_balance = $accountdata['total_balance'];
	$income_spent = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $accountdata['income_spent'], $ilconfig['globalserverlocale_defaultcurrency']);
	$income_received = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $accountdata['income_reported'], $ilconfig['globalserverlocale_defaultcurrency']);
	$SQL = $ilance->db->query("
		SELECT cc_id, date_added, date_updated, user_id, creditcard_number, creditcard_expiry, cvv2, name_on_card, phone_of_cardowner, email_of_cardowner, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country, creditcard_status, default_card, creditcard_type, authorized, auth_amount1, auth_amount2, attempt_num, trans1_id, trans2_id
		FROM " . DB_PREFIX . "creditcards
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($SQL) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($SQL, DB_ASSOC))
		{
			$dec_CardNumber = $ilance->crypt->three_layer_decrypt($row['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
			$ccnum_hidden = substr_replace($dec_CardNumber, '... XXXX ', 0 , (mb_strlen($dec_CardNumber) - 4));
			$row['creditcard_hidden'] = $ccnum_hidden;
			if ($row['authorized'] == 'no')
			{
				if ($row['creditcard_status'] == 'active')
				{
					if ($row['auth_amount1'] == 0 AND $row['auth_amount2'] == 0)
					{
						$crypted = array('cmd' => '_auth-creditcard', 'cc_id' => $row['cc_id']);
						$row['creditcard_status'] = '<a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" title="{_authenticate_card}">{_authenticate_card}</a>';
						unset($crypted);
						$crypted = array('cmd' => '_update-creditcard', 'cc_id' => $row['cc_id']);
						$row['creditcard_action'] = '<span title="{_edit_card}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" title="{_edit_card}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="{_edit_card}" /></a></span>&nbsp;&nbsp;&nbsp;';
						unset($crypted);
						$crypted = array('cmd' => '_remove-creditcard', 'cc_id' => $row['cc_id']);
						$row['creditcard_action'] .= '<span title="{_remove_card}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" onclick="return confirm_js(\'{_credit_card_removal}\')" title="{_remove_card}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_remove_card}" /></a></span>';
						unset($crypted);
						$authorized_none = 0;
						$authorized_ccnum_hidden = $ccnum_hidden;
					}
					else if ($row['auth_amount1'] > 0 AND $row['auth_amount2'] > 0)
					{
						$crypted = array('cmd' => '_auth-creditcard-final', 'cc_id' => $row['cc_id']);
						$row['creditcard_status'] = '<span title="{_finish_authentication}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" title="{_finish_authentication}">{_finish_authentication}</a></span>';
						unset($crypted);
						$crypted = array('cmd' => '_update-creditcard', 'cc_id' => $row['cc_id']);
						$row['creditcard_action'] = '<span title="{_edit_card}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" title="{_edit_card}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="{_edit_card}"></a></span>&nbsp;&nbsp;&nbsp;';
						unset($crypted);
						$crypted = array('cmd' => '_remove-creditcard', 'cc_id' => $row['cc_id']);
						$row['creditcard_action'] .= '<span title="{_remove_card}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" onclick="return confirm_js(\'{_credit_card_removal}\')" title="{_remove_card}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_remove_card}" /></a></span>';
						unset($crypted);
						$authorized_ccnum_hidden = $ccnum_hidden;
					}
				}
			}
			else
			{
				if ($row['creditcard_status'] == 'active')
				{
					$row['creditcard_status'] = '<span title="{_card_authenticated}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" id="" /> {_card_authenticated}</span>';
					$crypted = array('cmd' => '_update-creditcard', 'cc_id' => $row['cc_id']);
					$row['creditcard_action'] = '<span title="{_edit_card}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" title="{_edit_card}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="{_edit_card}" /></a></span>&nbsp;&nbsp;&nbsp;';
					unset($crypted);
					$crypted = array('cmd' => '_remove-creditcard', 'cc_id' => $row['cc_id']);
					$row['creditcard_action'] .= '<span title="{_remove_card}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" onclick="return confirm_js(\'{_credit_card_removal}\')" title="{_remove_card}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_remove_card}" /></a></span>';
					unset($crypted);
				}
				else if ($row['creditcard_status'] == 'expired')
				{
					$row['creditcard_status'] = '{_expired}';
					$crypted = array('cmd' => '_update-creditcard', 'cc_id' => $row['cc_id']);
					$row['creditcard_action'] = '<span title="{_edit_card}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" title="{_edit_card}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="{_edit_card}" /></a></span>&nbsp;&nbsp;&nbsp;';
					unset($crypted);
					$crypted = array('cmd' => '_remove-creditcard', 'cc_id' => $row['cc_id']);
					$row['creditcard_action'] .= '<span title="{_remove_card}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" onclick="return confirm_js(\'{_credit_card_removal}\')" title="{_remove_card}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_remove_card}" /></a></span>';
					unset($crypted);
				}
			}
			if ($row['creditcard_type'] == 'visa')
			{ 
				$row['creditcard_type'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="{_visa}" />';
			}			
			if ($row['creditcard_type'] == 'amex')
			{
				$row['creditcard_type'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="{_american_express}" />';
			} 
			if ($row['creditcard_type'] == 'mc')
			{ 
				$row['creditcard_type'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="{_master_card}" />';
			} 
			if ($row['creditcard_type'] == 'disc')
			{ 
				$row['creditcard_type'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="{_discover}" />';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$creditcard_rows[] = $row;
			$row_count++;
		}
		$show['no_creditcard_rows_returned'] = false;
	}
	else
	{
		$show['no_creditcard_rows_returned'] = true;
	}
	$SQL = $ilance->db->query("
		SELECT bank_id, user_id, beneficiary_account_name, destination_currency_id, beneficiary_bank_name, beneficiary_account_number, beneficiary_bank_routing_number_swift, bank_account_type, beneficiary_bank_address_1, beneficiary_bank_address_2, beneficiary_bank_city, beneficiary_bank_state, beneficiary_bank_zipcode, beneficiary_bank_country_id, wire_bin_type
		FROM " . DB_PREFIX . "bankaccounts
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($SQL) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($SQL, DB_ASSOC))
		{
			$row['beneficiary_account_number'] = substr_replace($row['beneficiary_account_number'], '... XXXX ', 0 , (mb_strlen($row['beneficiary_account_number']) - 4));
			$row['bank_account_status'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_account_verified}" /> {_account_verified}';
			$crypted = array('cmd' => '_update-bankaccount', 'bankid' => $row['bank_id']);
			$row['bank_account_action'] = '<span title="{_edit_account}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="{_edit_account}"></a></span>&nbsp;&nbsp;&nbsp;';
			unset($crypted);
			$crypted = array('cmd' => '_remove-bankaccount', 'bankid' => $row['bank_id']);
			$row['bank_account_action'] .= '<span title="{_remove_account}"><a href="' . $ilpage['accounting'] . '?crypted=' . encrypt_url($crypted) . '" onclick="return confirm_js(\'{_bank_account_removal}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_remove_account}" /></a></span>';
			unset($crypted);
			if ($row['bank_account_type'] == 'checking')
			{ 
				$row['bank_account_type'] = '{_checking}';
			} 
			else if ($row['bank_account_type'] == 'savings')
			{ 
				$row['bank_account_type'] = '{_savings}';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$bankaccount_rows[] = $row;
			$row_count++;
		}
		$show['no_bankaccount_rows_returned'] = false;
	}
	else
	{
		$show['no_bankaccount_rows_returned'] = true;
	}
	$show['wiretransfer_active'] = false;       
	if ($ilconfig['enable_bank_deposit_support'])
	{
		$show['wiretransfer_active'] = true;
	}
	$available_balance = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $available_balance, $ilconfig['globalserverlocale_defaultcurrency']);
	$total_balance = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $total_balance, $ilconfig['globalserverlocale_defaultcurrency']);
	$subscription_alerts = $ilance->subscription->alerts($_SESSION['ilancedata']['user']['userid']);
	$autopayment = (int)fetch_user('autopayment', $_SESSION['ilancedata']['user']['userid']);
	$autopayments_pulldown = construct_pulldown('autopayment', 'autopayment', array('1' => '{_yes}', '0' => '{_no}'), $autopayment, 'style="font-family: verdana" class="smaller"');
	$pprint_array = array('autopayments_pulldown','income_spent','income_received','subscription_alerts','authorized_ccnum_hidden','remote_addr','referer','account_number','available_balance','total_balance','login_include','page','prevnext','authorized');
    
	($apihook = $ilance->api('accounting_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'accounting.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'creditcard_rows');
	$ilance->template->parse_loop('main', 'bankaccount_rows');
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