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
* Payment.
*
* @package      iLance\Payment
* @version      4.0.0.8059
* @author       ILance
*/
class payment
{
	/**
	* Function to fetch and print order id radio combo buttons for a listing payment selection process
	*
	* @param       integer        listing id
	* @param       integer        buyer id
	* @param       integer        order id (if applicable)
	*
	* @return      string         Returns HTML formatted string of radio including html markup
	*/
	function print_orderid_methods($pid = 0, $buyerid = 0, $orderid = 0)
	{
		global $ilance, $show, $phrase, $headinclude, $onsubmit, $orderidradiocount;
		$show['multipleorders'] = false;
		$html = '';
		$count = 0;
		$orderidradiocount = 0;
		$currencyid = fetch_auction('currencyid', $pid);
		$sql = $ilance->db->query("
			SELECT orderid, ship_location, amount
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . intval($pid) . "'
				AND buyer_id = '" . intval($buyerid) . "'
				AND paiddate = '0000-00-00 00:00:00'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$orderidradiocount = 1;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$checked = (($orderid > 0 AND $orderid == $res['orderid']) ? 'checked="checked"' : '');
				$html .= '<div style="padding-top:4px; padding-left:9px"><label for=""><input type="radio" name="orderid" id="orderid_' . $orderidradiocount . '" value="' . $res['orderid'] . '" ' . $checked . ' onclick="toggle_show(\'methods_wrapper\')" /> <span class="black">' . '{_order}' . ' ID <span class="blue">#' . $res['orderid'] . '</span> ' . '{_to}' . ' <span>' . handle_input_keywords($res['ship_location']) . '</span> - ' . $ilance->currency->format($res['amount'], $currencyid) . '</span></label></div>';
				$count++;
				$orderidradiocount++;
			}
		}
		if ($count > 1)
		{
			$show['multipleorders'] = true;
		}
		return $html;
	}
    
	/**
	* Function to print the payment method title for an auction
	* 
	* This function will print the payment method title
	*
	* @param       integer        auction listing id
	*
	* @return      string         Returns HTML formatted string
	*/
	function print_payment_method_title($projectid = 0)
	{
		global $ilance, $phrase, $ilconfig, $ilpage;
		$html = '';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "filter_escrow, filter_gateway, filter_ccgateway, paymethodcc, filter_offline, paymethod, paymethodoptions, paymethodoptionsemail
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['filter_escrow'])
			{
				$html = 'escrow';
			}
			if ($res['filter_gateway'] == '1')
			{
				if (is_serialized($res['paymethodoptions']))
				{
					$paymethodoptions = unserialize($res['paymethodoptions']);
					foreach ($paymethodoptions AS $paymethodoption => $value)
					{
						$html = 'gateway_' . str_replace(' ', '_', mb_strtolower($paymethodoption));
					}
				}
				else if (!empty($res['paymethod']))
				{
					$html = 'gateway_' . str_replace(' ', '_', mb_strtolower($res['paymethod']));
				}
			}
			if ($res['filter_ccgateway'] == '1')
			{
				if (is_serialized($res['paymethodcc']))
				{
					$paymethodoptions = unserialize($res['paymethodcc']);
					foreach ($paymethodoptions AS $paymethodoption => $value)
					{
						$html = 'ccgateway_' . str_replace(' ', '_', mb_strtolower($paymethodoption));
					}
				}
				else if (!empty($res['paymethodcc']))
				{
					$html = 'ccgateway_' . str_replace(' ', '_', mb_strtolower($res['paymethodcc']));
				}
			}
			if ($res['filter_offline'])
			{
				if (is_serialized($res['paymethod']))
				{
					$paymethods = unserialize($res['paymethod']);
					foreach ($paymethods AS $paymethod)
					{
						$html = 'offline_' . str_replace(' ', '_', mb_strtolower($paymethod));
					}
				}
				else
				{
					$html = 'offline_' . str_replace(' ', '_', mb_strtolower($res['paymethod']));
				}
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the email address associated with a selected payment gateway selected by the seller
	* 
	* This function will print a email address if applicable.
	*
	* @param       integer        auction listing id
	* @param       string         selected payment gateway
	*
	* @return      string         Returns HTML formatted string
	*/
	function fetch_payment_method_email($projectid = 0, $selectedgateway = '')
	{
		global $ilance;
		$paymethodoptionsemail = '';
		$sql = $ilance->db->query("
			SELECT paymethodoptionsemail
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "' 
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$paymethodoptionsemail = $res['paymethodoptionsemail'];
		}
		if (is_serialized($paymethodoptionsemail))
		{
			$options = unserialize($paymethodoptionsemail);
			if (is_array($options))
			{
				foreach ($options AS $gateway => $email)
				{
					if (isset($gateway) AND $gateway == $selectedgateway)
					{
						if (!empty($email))
						{
							return trim($email);
						}
					}
				}
			}
		}
		return false;
	}
    
	/**
	* Function to print a fixed payment method recognized by ILance
	* 
	* @param       string         selected payment method
	* @param       boolean        show the pay method type in the string?
	*
	* @return      string         Returns HTML formatted string
	*/
	function print_fixed_payment_method($selected = '', $showtype = true)
	{
		global $phrase, $show;
		$show['depositlink'] = true;
		$html = '';
		if (mb_substr($selected, 0, 8) == 'gateway_')
		{
			$show['depositlink'] = false;
			$varname = mb_substr($selected, 7);
			$varname = (mb_substr($varname, 0, 1) == '_' ? $varname : '_' . $varname);
			$html = '{' . $varname . '}';
			$html .= $showtype ? ' ({_direct_payment})' : '';
		}
		else if (mb_substr($selected, 0, 10) == 'ccgateway_')
		{
			$show['depositlink'] = false;
			$varname = mb_substr($selected, 9);
			$varname = (mb_substr($varname, 0, 1) == '_' ? $varname : '_' . $varname);
			$html = '{' . $varname . '}';
			$html .= $showtype ? ' ({_credit_card})' : '';
		}
		else if (mb_substr($selected, 0, 8) == 'offline_')
		{
			$show['depositlink'] = false;
			$varname = mb_substr($selected, 8);
			$varname = (mb_substr($varname, 0, 1) == '_' ? $varname : '_' . $varname);
			$html = '{' . $varname . '}';
			$html .= $showtype ? ' ({_offline_payment})' : '';
		}
		else if (strchr($selected, 'escrow') OR $selected == 'escrow')
		{
			$show['depositlink'] = true;
			$html = SITE_NAME . ' {_escrow_service}';
			$html .= $showtype ? ' ({_direct_payment})' : '';
		}
		return $html;
	}
    
	/**
	* Function to print out a listings pre-defined payment methods.
	* 
	* This function can be used to show payment methods in a string or used to generate checkboxes based on a buyer payment selector process.
	*
	* @param       integer        listing id
	* @param       boolean        print radio button logic (default false)
	* @param       boolean        return the number of payment options only (default false)
	* @param       string         css class name to use for displaying text output
	* @param       string         custom identifier (optional)
	* @param       string         javascript code for onclick for each method (optional)
	* @param       string         field name for input form (default paymethod)
	* @param       boolean        display the payment type beside the payment method (ie: PayPal (offsite payment) vs. PayPal) default false
	* @param       boolean        only use hidden fields instead of radio buttons (default false)
	* @param       boolean        show escrow fee bit beside the payment method radio button (default false)
	* @param       integer        pre-total amount (used for add-ons)
	*
	* @return      string         Returns HTML formatted string of payment method output or radio button input logic
	*/
	function print_payment_methods($projectid = 0, $radiobuttons = false, $countonly = false, $textclass = '', $customid = '', $onclickjs = '', $fieldname = 'paymethod', $showpaymenttype = false, $hiddenfieldonly = false, $showbuyerescrowfee = false, $pretotal = 0)
	{
		global $ilance, $phrase, $ilconfig, $ilpage, $paymentmethodradios_js, $show;
		$count = 0;
		$html = '';
		$sql = $ilance->db->query("
			SELECT filter_escrow, filter_gateway, filter_ccgateway, filter_offline, paymethod, paymethodcc, paymethodoptions, paymethodoptionsemail
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "'
		", 0, null, __FILE__, __LINE__);
		$num = $ilance->db->num_rows($sql);
		if ($num > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$checked = '';
			if ($res['filter_escrow'])
			{
				$count++;
				if ($countonly == false)
				{
					if ($radiobuttons)
					{
						$tch = 'escrow';
						if ($hiddenfieldonly)
						{
							$html .= '<input type="hidden" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="escrow" />';
						}
						else
						{
							$escrowfee = $escrowfeetax = 0;
							$escrowtax = '';
							if ($showbuyerescrowfee)
							{
								$escrowfee = 0;
								if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
								{
									// fixed escrow cost to buyer
									$escrowfee = floatval($ilconfig['escrowsystem_bidderfixedprice']);
								}
								else
								{
									if ($ilconfig['escrowsystem_bidderpercentrate'] > 0 AND $pretotal > 0)
									{
										$escrowfee = floatval($pretotal * $ilconfig['escrowsystem_bidderpercentrate'] / 100);
									}
								}
								if ($escrowfee > 0)
								{
									if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'commission'))
									{
										$escrowfeetax = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $escrowfee, 'commission', 0);
									}
									if ($escrowfeetax)
									{
										$escrowtax = ' (<span title="{_escrow} {_tax}">+ ' . $ilance->currency->format($escrowfeetax) . '</span>)';
									}
								}
								else
								{
									$showbuyerescrowfee = false;
								}
							}
							if (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == 'escrow')
							{
								$checked = ' checked="checked"';
							}
							$html .= '<div style="padding-top:4px; padding-left:10px" class="' . $textclass . '"><label for=""><input type="radio" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="escrow" ' . $checked . ' onclick="' . $onclickjs . '" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_blue.png" border="0" alt="" /> ' . SITE_NAME . ' {_escrow_service} ' . (($showpaymenttype) ? '({_direct_payment})' : '') . (($showbuyerescrowfee) ? ' - {_escrow_fee}: <span title="{_escrow_fee}">' . $ilance->currency->format($escrowfee) . '</span>' . $escrowtax . '' : '') . '</label></div>';
							$checked = '';
							$paymentmethodradios_js .= '	 if (fetch_js_object(\'paymethod_' . $count . $customid . '\').checked == true)
{
	haveerror = false;
}
';
						}
						unset($tch);
					}
					else
					{
					    $html .= '<span style="float:{template_textalignment}" title="' . SITE_NAME . ' {_escrow_service}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_blue.png" border="0" alt="" /></span>, ';
					}
				}
			}
			if ($res['filter_gateway'])
			{
				if (is_serialized($res['paymethodoptions']))
				{
					$paymethodoptions = unserialize($res['paymethodoptions']);
					foreach ($paymethodoptions AS $paymethodoption => $value)
					{
						$count++;
						if ($countonly == false)
						{
							if ($radiobuttons)
							{
								$tch = 'gateway_' . str_replace(' ', '_', mb_strtolower($paymethodoption));
								if ($hiddenfieldonly)
								{
									$html .= '<input type="hidden" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="gateway_' . str_replace(' ', '_', mb_strtolower($paymethodoption)) . '" />';
								}
								else
								{
									$html .= '<div style="padding-top:4px; padding-left:10px" class="' . $textclass . '"><label for=""><input type="radio" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="gateway_' . str_replace(' ', '_', mb_strtolower($paymethodoption)) . '" ' . $checked . ' onclick="' . $onclickjs . '" /> {_' . $paymethodoption . '} ' . (($showpaymenttype) ? '({_direct_payment})' : '') . '</label></div>';
									$paymentmethodradios_js .= '	 if (fetch_js_object(\'paymethod_' . $count . $customid . '\').checked == true)
{
	haveerror = false;
}
';
								}
								unset($tch);
							}
							else
							{
							    $html .= '{_' . $paymethodoption . '}, ';
							}
						}
					}
				}
				else if (!empty($res['paymethod']))
				{
					$count++;
					if ($countonly == false)
					{
						if ($radiobuttons)
						{
							$tch = 'gateway_' . str_replace(' ', '_', mb_strtolower($res['paymethod']));
							if ($hiddenfieldonly)
							{
								$html .= '<input type="hidden" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="gateway_' . str_replace(' ', '_', mb_strtolower($res['paymethod'])) . '" />';
							}
							else
							{
								$html .= '<div style="padding-top:4px; padding-left:10px" class="' . $textclass . '"><label for=""><input type="radio" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="gateway_' . str_replace(' ', '_', mb_strtolower($res['paymethod'])) . '" ' . $checked . ' onclick="' . $onclickjs . '" /> ' . $res['paymethod'] . ' ' . (($showpaymenttype) ? '({_direct_payment})' : '') . '</label></div>';
								$paymentmethodradios_js .= '	 if (fetch_js_object(\'paymethod_' . $count . $customid . '\').checked == true)
{
	haveerror = false;
}
';
							}
							unset($tch);
						}
						else
						{
							$html .= $res['paymethod'];
						}
					}
				}
			}
			if ($res['filter_ccgateway'])
			{
				if (is_serialized($res['paymethodcc']))
				{
					$paymethodccs = unserialize($res['paymethodcc']);
					foreach ($paymethodccs AS $paymethodcc => $value)
					{
						$count++;
						if ($countonly == false)
						{
							if ($radiobuttons)
							{
								$tch = 'ccgateway_' . str_replace(' ', '_', mb_strtolower($paymethodcc));
								if ($hiddenfieldonly)
								{
									$html .= '<input type="hidden" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="ccgateway_' . str_replace(' ', '_', mb_strtolower($paymethodcc)) . '" />';
								}
								else
								{
									$html .= '<div style="padding-top:4px; padding-left:10px" class="' . $textclass . '"><label for=""><input type="radio" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="ccgateway_' . str_replace(' ', '_', mb_strtolower($paymethodcc)) . '" ' . $checked . ' onclick="' . $onclickjs . '" /> {_' . $paymethodcc . '} ' . (($showpaymenttype) ? '({_direct_payment})' : '') . '</label></div>';
									$paymentmethodradios_js .= '	 if (fetch_js_object(\'paymethod_' . $count . $customid . '\').checked == true)
{
	haveerror = false;
}
';
								}
								unset($tch);
							}
							else
							{
							    $html .= '{_' . $paymethodcc . '}, ';
							}
						}
					}
				}
			}
			if ($res['filter_offline'])
			{
				if (is_serialized($res['paymethod']))
				{
					$paymethods = unserialize($res['paymethod']);
					if (isset($paymethods) AND is_array($paymethods))
					{
						foreach ($paymethods AS $paymethod)
						{
							$count++;
							if ($countonly == false)
							{
								if ($radiobuttons)
								{
									if ($hiddenfieldonly)
									{
										$html .= '<input type="hidden" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="offline_' . $paymethod . '" />';
									}
									else
									{
										if (isset($ilance->GPC['paymethod']) AND mb_strpos($ilance->GPC['paymethod'], $paymethod))
										{
											$checked = ' checked="checked"';
										}
										$html .= '<div style="padding-top:4px; padding-left:10px" class="' . $textclass . '"><label for=""><input type="radio" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="offline_' . $paymethod . '" ' . $checked . ' onclick="' . $onclickjs . '" /> {' . $paymethod . '} ' . (($showpaymenttype) ? '({_offsite_payment})' : '') . '</label></div>';
										$checked = '';
										$paymentmethodradios_js .= '	 if (fetch_js_object(\'paymethod_' . $count . $customid . '\').checked == true)
{
	haveerror = false;
}
';
									}
								}
								else
								{
								    $html .= '{' . $paymethod . '}, ';
								}
							}
						}
					}
				}
				else
				{
					$count++;
					if ($countonly == false)
					{
						if ($radiobuttons)
						{
							if ($hiddenfieldonly)
							{
								$html .= '<input type="hidden" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="offline_' . $res['paymethod'] . '" />';
							}
							else
							{
								$html .= '<div style="padding-top:4px" class="' . $textclass . '"><label for=""><input type="radio" name="' . $fieldname . '" id="paymethod_' . $count . $customid . '" value="offline_' . $res['paymethod'] . '" ' . $checked . ' onclick="' . $onclickjs . '" /> {' . $res['paymethod'] . '} ' . (($showpaymenttype) ? '({_offsite_payment})' : '') . '</label></div>';
								$paymentmethodradios_js .= '	 if (fetch_js_object(\'paymethod_' . $count . $customid . '\').checked == true)
{
	haveerror = false;
}
';
							}
						}
						else
						{
							$html .= '{' . $res['paymethod'] . '}';
						}
					}
				}
			}
		}
		if (!empty($html) AND $radiobuttons == false)
		{
			$html = substr($html, 0, -2);
		}
		return $countonly ? $count : $html;
	}
    
	/**
	* Function to mark a outside direct pay listing as paid (seller invokes this himself)
	*
	* @return      nothing
	*/
	function mark_listing_as_paid($pid = 0, $bid = 0, $winnermarkedaspaidmethod = '')
	{
		global $ilance, $phrase, $ilconfig;
		$extra = !empty($winnermarkedaspaidmethod) ? "winnermarkedaspaidmethod = '" . $ilance->db->escape_string($winnermarkedaspaidmethod) . "'," : '';
		$project_details = fetch_auction('project_details', $pid);
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_bids
			SET winnermarkedaspaid = '1',
			$extra
			winnermarkedaspaiddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND bid_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_realtimebids
			SET winnermarkedaspaid = '1',
			$extra
			winnermarkedaspaiddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND bid_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>