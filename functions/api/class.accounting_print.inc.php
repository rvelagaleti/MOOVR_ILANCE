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

/**
* Accounting class to perform the majority of accounting functions within ILance.
*
* @package      iLance\Accounting\Print
* @version      4.0.0.8059
* @author       ILance
*/
class accounting_print extends accounting
{
	/**
	* Function for printing only the <option> values for active credit cards on file for the user.
	*
	* @param       integer        user id
	*
	* @return      string         HTML representation of the options
	*/
	function print_active_creditcard_options($userid = 0)
	{
		global $ilance, $ilconfig, $phrase;
		if ($userid > 0)
		{
			$html = '';
			if ($ilconfig['save_credit_cards'] AND $ilconfig['use_internal_gateway'] != 'none')
			{
				$sql = $ilance->db->query("
					SELECT cc_id, creditcard_number, creditcard_type
					FROM " . DB_PREFIX . "creditcards
					WHERE user_id = '" . intval($userid) . "'
						AND creditcard_status = 'active'
						AND authorized = 'yes'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$html .= '<optgroup label="{_active_credit_cards}">';
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$html .= '<option value="' . $res['cc_id'] . '">';
						if ($res['creditcard_type'] == 'visa')
						{
							$html .= '{_credit_card} (VISA # ';
						}
						else if ($res['creditcard_type'] == 'amex')
						{
							$html .= '{_credit_card} (AMEX # ';
						}
						else if ($res['creditcard_type'] == 'mc')
						{
							$html .= '{_credit_card} (MC # ';
						}
						else if ($res['creditcard_type'] == 'disc')
						{
							$html .= '{_credit_card} (DISC # ';
						}
						$dec = $ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
						$dec = str_replace(' ', '', $dec);
						$html .= substr_replace($dec, 'XX XXXX XXXX ', 2, (mb_strlen($dec) - 6)) . ')</option>';
					}
					$html .= '</optgroup>';
				}
				// option to show credit card form (if saving of cards to db is disabled)
				$html .= '<optgroup label="{_electronic_payment}">';
				$html .= '<option value="ccform">{_pay_by_credit_card}</option>';
				$html .= '</optgroup>';
			}
			else
			{
				if ($ilconfig['use_internal_gateway'] != 'none')
				{
					// option to show credit card form (if saving of cards to db is disabled)
					$html .= '<optgroup label="{_electronic_payment}">';
					$html .= '<option value="ccform">{_pay_by_credit_card}</option>';
					$html .= '</optgroup>';
				}
			}
			return $html;
		}
		return false;
	}
    
	/**
	* Function for printing only the <option> values for active bank deposit accounts file for the user.
	* Additionally, this function will present a withdrawal fee defined by the admin within the admincp.
	* 
	* @param       integer        user id
	*
	* @return      string         HTML representation of the options
	*/
	function print_active_bankaccount_options($userid = 0)
	{
		global $ilance, $ilconfig, $phrase;
		if ($userid > 0 AND $ilconfig['enable_bank_deposit_support'])
		{
			$html = '';
			$sql = $ilance->db->query("
				SELECT beneficiary_account_number, beneficiary_bank_name, bank_account_type
				FROM " . DB_PREFIX . "bankaccounts
				WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$feebit = '';
				if ($ilconfig['bank_withdraw_fee_active'] AND $ilconfig['bank_withdraw_fee'] > 0)
				{
					$feebit = ' (+ ' . $ilance->currency->format($ilconfig['bank_withdraw_fee']) . ')';
				}
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$type = $res['bank_account_type'];
					$bankaccounttype = "{_" . $type . "}";
					$html .= '<option value="' . $res['beneficiary_account_number'] . '">' . ucwords(mb_strtolower($res['beneficiary_bank_name'])) . ' - ' . $bankaccounttype . ' #' . str_repeat('X', (mb_strlen($res['beneficiary_account_number']) - 4)) . mb_substr($res['beneficiary_account_number'], -4, 4) . $feebit . '</option>';
				}
				unset($type);
			}
	    
			($apihook = $ilance->api('print_active_bankaccount_options_end')) ? eval($apihook) : false;
	    
			return $html;
		}
		return false;
	}
    
	/**
	* Function for printing only the <option> values for enabled ipn gateway processor methods
	*
	* @param       string         the custom location we are parsing this function from
	*
	* @return      string         HTML representation of the options
	*/
	function print_active_ipn_options($area = '')
	{
		global $ilance, $ilconfig, $phrase;
		$html = '';
	
		($apihook = $ilance->api('print_active_ipn_options_start')) ? eval($apihook) : false;
	
		if ($ilconfig['paypal_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
		{
			$html .= '<option value="paypal">Paypal</option>';
		}
		if ($ilconfig['paypal_deposit_echeck_active'] AND !empty($area) AND $area == 'deposit')
		{
			$html .= '<option value="paypalecheck">Paypal eCheck</option>';
		}
		if ($ilconfig['cashu_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
		{
			$html .= '<option value="cashu">CashU</option>';
		}
		if ($ilconfig['moneybookers_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
		{
			$html .= '<option value="moneybookers">MoneyBookers</option>';
		}
		if ($ilconfig['platnosci_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
		{
			$html .= '<option value="platnosci">{_platnosci}</option>';
		}
	
		($apihook = $ilance->api('print_active_ipn_options_option')) ? eval($apihook) : false;
	
		if (!empty($html))
		{
			$html = '<optgroup label="{_online_payment}">' . $html . '</optgroup>';
		}
	
		($apihook = $ilance->api('print_active_ipn_options_end')) ? eval($apihook) : false;
	
		return $html;
	}
    
	/**
	* Function for printing only the <option> values for enabled offline deposit processor methods
	*
	* @return      string         HTML representation of the options
	*/
	function print_active_offline_deposit_methods()
	{
		global $ilance, $ilconfig, $headinclude;
		$html = $options = '';
		$js = '';
	
		($apihook = $ilance->api('print_active_offline_deposit_options_start')) ? eval($apihook) : false;
	
		$sql = $ilance->db->query("
			SELECT id, name, number, swift, company_name, company_address, custom_notes, fee, visible, sort
			FROM " . DB_PREFIX . "deposit_offline_methods 
			WHERE visible = '1'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$options .= '<option value="offline_' . $res['id'] . '">' . $res['name'] . '</option>';
				$js .= 'deposit_fees[' . $res['id'] . '] = ' . $res['fee'] . ';';
			}
		}
		if (!empty($options) AND $ilconfig['invoicesystem_enableoffsitedepositpayment'] == '1')
		{
			$html = '<optgroup label="{_offline_payment}">' . $options . '</optgroup>';
			$headinclude .= '<script type="text/javascript">
<!--
' . $js . '
//-->
</script>';
		}
	
		($apihook = $ilance->api('print_active_offline_deposit_options_end')) ? eval($apihook) : false;
	
		return $html;
	}
    
	/**
	* Function for printing the appropriate payment processor options within the payment menu pulldown's
	*
	* @param       string         the custom location we are parsing this function from
	* @param       string         fieldname of the pulldown selection menu (paymethod is default)
	* @param       integer        user id
	* @param       string         javascript string to include in the onchange event
	*
	* @return      string         HTML representation of the pulldown menu
	*/
	function print_paymethod_pulldown($location = '', $fieldname = 'paymethod', $userid = 0, $javascript = '')
	{
		global $ilance, $phrase, $ilconfig, $show;
		$html = '';
		if (isset($location))
		{
			switch ($location)
			{
				// #### SUBSCRIPTION MENU ##############################
				case 'subscription':
				{
					break;
				}
				// #### DEPOSIT MENU ###################################
				case 'deposit':
				{
					$html .= '<select name="' . $fieldname . '" style="font-family: verdana" ' . $javascript . '>';
					$html .= '<option value="">{_please_select}</option>';
					$html .= $this->print_active_creditcard_options($userid);
					$html .= $this->print_active_ipn_options('deposit');
					$html .= $this->print_active_offline_deposit_methods();
					$html .= '</select>';
					break;
				}
				// #### WITHDRAWAL MENU ################################
				case 'withdraw':
				{
					$html .= '<select name="' . $fieldname . '" style="font-family: verdana" ' . $javascript . '>';
					if ($ilconfig['checkpayout_support'])
					{
						// any withdraw fees active?
						$feebit = '';
						if ($ilconfig['check_withdraw_fee_active'] AND $ilconfig['check_withdraw_fee'] > 0)
						{
							$feebit = ' (+ ' . $ilance->currency->format($ilconfig['check_withdraw_fee']) . ')';
						}
						$html .= '<optgroup label="' . '{_postal_mail}' . '">';
						$html .= '<option value="check">' . '{_check_money_order}' . $feebit . '</option>';
						$html .= '</optgroup>';
					}
					$html .= '<optgroup label="' . '{_electronic_transfer}' . '">';
					if ($ilconfig['paypal_withdraw_active'])
					{
						// any withdraw fees active?
						$feebit = '';
						if ($ilconfig['paypal_withdraw_fee_active'] AND $ilconfig['paypal_withdraw_fee'] > 0)
						{
							$feebit = ' (+ ' . $ilance->currency->format($ilconfig['paypal_withdraw_fee']) . ')';
						}
						$html .= '<option value="paypal">' . '{_paypal_money_request}' . $feebit . '</option>';
					}
					// #### ACTIVE BANK DEPOSIT ACCOUNTS ON FILE ###################
					$html .= $this->print_active_bankaccount_options($userid);
					$html .= '</optgroup>';
					$html .= '</select>';
					break;
				}
				// #### DIRECT PAYMENT MENU ############################
				case 'invoicepayment':
				{
					$html .= '<select name="' . $fieldname . '" style="font-family: verdana" ' . $javascript . '>';
					$accountbalanceallowed = true;
		    
					($apihook = $ilance->api('print_paymethod_pulldown_invoicepayment_start')) ? eval($apihook) : false;
		    
					// #### ONLINE ACCOUNT BALANCE #################
					if ($accountbalanceallowed)
					{
						$html .= '<optgroup label="{_available_balance}">';
						$html .= '<option value="account">' . SITE_NAME . ' {_online_account_instant_payment} ({_available_balance}: ' . $ilance->currency->format(fetch_user('available_balance', intval($userid))) . ')</option>';
						$html .= '</optgroup>';
					}
		    
					($apihook = $ilance->api('print_paymethod_pulldown_invoicepayment_end')) ? eval($apihook) : false;
		    
					// #### IPN GATEWAYS ###########################
					$html .= $this->print_active_ipn_options('invoicepayment');
					$html .= '</select>';
					break;
				}
				// #### ONLINE ACCOUNT BALANCE ONLY ####################
				case 'portfolio':
				case 'account':
				{
					$html .= '<select name="' . $fieldname . '" style="font-family: Verdana" ' . $javascript . '>';
					$html .= '<optgroup label="{_available_balance}">';
					$html .= '<option value="account">' . SITE_NAME . ' {_online_account_instant_payment} ({_available_balance}: ' . $ilance->currency->format(fetch_user('available_balance', intval($userid))) . ')</option>';
					$html .= '</optgroup>';
					$html .= '</select>';
					break;
				}
			}
		}
		return $html;
	}
    
	/**
	* Function to print out a payment method icon
	*
	* @return      string       HTML formatted image (<img> tag)
	*/
	function print_paymethod_icon($paymethod = 'account', $showicon = true)
	{
		global $ilance, $phrase, $ilconfig;
		if ($showicon)
		{
			$html = '<span title="{_' . $paymethod . '}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $paymethod . '.gif" border="0" alt="{_' . $paymethod . '}" /></span>';
		}
		else
		{
			$html = "{_" . $paymethod . "}";
		}
		return $html;
	}
    
	/**
	* Function to print out a payment method funding source based on a valid transaction
	*
	* @param       integer      invoice id
	* 
	* @return      string       HTML formatted image (<img> tag)
	*/
	function print_paymethod_source($invoiceid = 0)
	{
		global $ilance, $phrase, $ilconfig;
		// sources: Account, Escrow,
		$html = '';
		return $html;
	}
    
	/**
	* Function to print out a payment method funding target based on a valid transaction
	*
	* @param       integer      invoice id
	* 
	* @return      string       HTML formatted image (<img> tag)
	*/
	function print_paymethod_target($invoiceid = 0)
	{
		global $ilance, $phrase, $ilconfig;
		// SITE_NAME, Account, Escrow
		$html = '';
		return $html;
	}
    
	/**
	* Function to print out a payment method type based on a valid transaction
	*
	* @param       integer      invoice id
	* 
	* @return      string       HTML formatted image (<img> tag)
	*/
	function print_paymethod_method($invoiceid = 0)
	{
		global $ilance, $phrase, $ilconfig;
		// Debit or Credit
		$html = '';
		return $html;
	}
    
	/**
	* Function to print an invoice type phrase based on the currently selected language
	*
	* @param       string         invoice type (subscription, commission, p2b, buynow, credential, debit, credit, escrow, refund or storesubscription)
	*
	* @return      string         Returns formatted final value feee (if applicable)
	*/
	function print_transaction_type($invoicetype = '')
	{
		global $ilance, $phrase, $ilconfig;
		$html = '';
		if (isset($invoicetype) AND !empty($invoicetype))
		{
			switch ($invoicetype)
			{
				case 'p2b':
				{
					$html = '{_generated_invoice}';
					break;
				}
				case 'buynow':
				{
					$html = '{_buy_now}';
					break;
				}
				case 'storesubscription':
				{
					$html = '{_store_subscriptions}';
					break;
				}		
				case 'subscription':
				{
					$html = '{_subscription}';
					break;
				}		
				case 'commission':
				{
					$html = '{_commission}';
					break;
				}		
				case 'credential':
				{
					$html = '{_credential}';
					break;
				}		
				case 'debit':
				{
					$html = '{_debit}';
					break;
				}	
				case 'escrow':
				{
					    $html = '{_account_debit}';
					    break;
				}
				case 'credit':
				{
					$html = '{_account_credit}';
					break;
				}
				case 'refund':
				{
					$html = '{_refund}';
					break;
				}
			}
	    
			($apihook = $ilance->api('print_transaction_type_end')) ? eval($apihook) : false;
		}
		return $html;
	}
    
	
	/**
	* Function to fetch and print the total income reported by a particular user
	*
	* @param        integer       user id
	* @param        integer       category id (optional to display earnings in that category only)
	*
	* @return	string        Returns the formatted income reported string (ie: USD$50,000.00)
	*/
	function print_income_reported($userid = 0, $noformat = false, $cid = 0)
	{
		global $ilance, $ilconfig, $phrase;
		$earnings = '-';
		$sql = $ilance->db->query("
			SELECT income_reported, displayfinancials
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$result = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($result['income_reported'] > 0)
			{
				$earnings = $ilance->currency->format($result['income_reported'], $ilconfig['globalserverlocale_defaultcurrency']);
				if ($noformat)
				{
					$earnings = $result['income_reported'];
				}
			}
			if ($result['displayfinancials'] == '0')
			{
				$earnings = '{_private}';
			}
		}
		return $earnings;
	}
    
	/**
	* Function to fetch and print the total income spent by a particular user
	*
	* @param        integer       user id
	*
	* @return	string        Returns the formatted income spent string (ie: USD$50,000.00)
	*/
	function print_income_spent($userid = 0)
	{
		global $ilance, $ilconfig;
		$sql = $ilance->db->query("
			SELECT income_spent AS spent
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$result = $ilance->db->fetch_array($sql, DB_ASSOC);
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			{
				$spent = $ilance->currency->format($result['spent'], $ilconfig['globalserverlocale_defaultcurrency']);
			}
			else
			{
				if (!empty($_SESSION['ilancedata']['user']['currencyid']))
				{
					$spent = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $result['spent']);
				}
				else
				{
					$spent = $ilance->currency->format($result['spent'], $ilconfig['globalserverlocale_defaultcurrency']);
				}
			}
		}
		else
		{
			$spent = $ilance->currency->format(0, $ilconfig['globalserverlocale_defaultcurrency']);
		}
		return $spent;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>