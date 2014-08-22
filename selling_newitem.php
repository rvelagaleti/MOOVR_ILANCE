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
if (!defined('LOCATION') OR defined('LOCATION') != 'selling')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
if ($ilconfig['globalauctionsettings_productauctionsenabled'] == 0 AND !empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
{
	print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
	exit();
}
if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createproductauctions') == 'no')
{
	$area_title = '{_viewing_access_denied_menu}';
	$page_title = SITE_NAME . ' - {_viewing_access_denied_menu}';
	print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('createproductauctions'));
	exit();
}
$show['error_product_questions'] = false;
// #### category question #############################################
if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
{
	foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
	{
		foreach ($answerarray AS $formname => $answer)
		{
			$checkanswer = $ilance->db->query("
				SELECT formdefault, formname, questionid
				FROM " . DB_PREFIX . "product_questions
				WHERE formname = '" . $ilance->db->escape_string($formname) . "'
					AND visible = '1'
					AND required = '1'
			");
			if ($ilance->db->num_rows($checkanswer) > 0)
			{
				$row = $ilance->db->fetch_array($checkanswer, DB_ASSOC);
				if (is_array($answer))
				{
					foreach ($answer AS $key => $value)
					{
						if ($value == $row['formdefault'])
						{
							$show['error_product_questions'] = true;
						}
						else
						{
							$_SESSION['ilancedata']['questions'][$formname] = $answer;
						}
					}
				}
				else
				{
					if ($answer == $row['formdefault'])
					{
						$show['error_product_questions'] = true;
					}
					else
					{
						$_SESSION['ilancedata']['questions'][$formname] = $answer;
					}
				}
			}
		}
	}
}
$show['eventtypechange'] = $show['escrowchange'] = $show['titlechange'] = $show['returnpolicychange'] = true;
$navcrumb = array ();
if ($ilconfig['globalauctionsettings_seourls'])
{
	$navcrumb[HTTP_SERVER . "sell"] = '{_sell}';
}
else
{
	$navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=selling"] = '{_sell}';
}
$show['bidsplaced'] = false;
$ilance->GPC['charityid'] = isset($ilance->GPC['charityid']) ? intval($ilance->GPC['charityid']) : 0;
$ilance->GPC['donation'] = isset($ilance->GPC['donation']) ? intval($ilance->GPC['donation']) : 0;
$ilance->GPC['donationpercentage'] = isset($ilance->GPC['donationpercentage']) ? intval($ilance->GPC['donationpercentage']) : 0;
// #### SUBMIT NEW PRODUCT AUCTION #####################################
if (isset($ilance->GPC['dosubmit']))
{
	if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $ilance->GPC['cid']) == false)
	{
		print_notice('{_this_is_a_nonposting_category}', '{_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings}', 'javascript:history.back(1);', '{_back}');
		exit();
	}
	unset($_SESSION['ilancedata']['tmp']['new_project_id']);
	$area_title = '{_saving_new_product_auction}';
	$page_title = SITE_NAME . ' - {_saving_new_product_auction}';
	if ($ilance->GPC['filtered_auctiontype'] == 'regular')
	{
		$ilance->GPC['startprice'] = (isset($ilance->GPC['startprice']) AND $ilance->GPC['startprice'] > 0) ? sprintf("%01.2f", $ilance->currency->string_to_number($ilance->GPC['startprice'])) : '0.01';
		$ilance->GPC['reserve_price'] = (isset($ilance->GPC['reserve_price']) AND $ilance->GPC['reserve_price'] > 0) ? sprintf("%01.2f", $ilance->currency->string_to_number($ilance->GPC['reserve_price'])) : '';
		$ilance->GPC['reserve'] = ($ilance->GPC['reserve_price'] > 0) ? '1' : '0';
		$ilance->GPC['buynow_price'] = (isset($ilance->GPC['buynow_price']) AND $ilance->GPC['buynow_price'] > 0) ? sprintf("%01.2f", $ilance->currency->string_to_number($ilance->GPC['buynow_price'])) : '';
		$ilance->GPC['buynow_qty'] = (isset($ilance->GPC['buynow_qty'])) ? intval($ilance->GPC['buynow_qty']) : '1';
		$ilance->GPC['buynow'] = ($ilance->GPC['buynow_price'] > 0) ? '1' : '0';
		$ilance->GPC['buynow_qty_lot'] = (isset($ilance->GPC['buynow_qty_lot_regular'])) ? intval($ilance->GPC['buynow_qty_lot_regular']) : '0';
		$ilance->GPC['items_in_lot'] = ($ilance->GPC['buynow_qty_lot'] == '1') ? (isset($ilance->GPC['items_in_lot_regular']) ? $ilance->GPC['items_in_lot_regular'] : '1') : '1';
		$ilance->GPC['classified_price'] = '';
		$ilance->GPC['classified_phone'] = '';
	
		($apihook = $ilance->api('selling_dosubmit_regular_end')) ? eval($apihook) : false;
	}
	else if ($ilance->GPC['filtered_auctiontype'] == 'fixed')
	{
		// fixed price only
		$ilance->GPC['buynow'] = '1';
		$ilance->GPC['reserve_price'] = '0';
		$ilance->GPC['reserve'] = '0';
		$ilance->GPC['buynow_price'] = (isset($ilance->GPC['buynow_price_fixed']) AND $ilance->GPC['buynow_price_fixed'] > 0) ? sprintf("%01.2f", $ilance->currency->string_to_number($ilance->GPC['buynow_price_fixed'])) : '';
		$ilance->GPC['buynow_qty'] = (isset($ilance->GPC['buynow_qty_fixed'])) ? intval($ilance->GPC['buynow_qty_fixed']) : '1';
		$ilance->GPC['startprice'] = sprintf("%01.2f", $ilance->currency->string_to_number($ilance->GPC['buynow_price']));
		$ilance->GPC['buynow_qty_lot'] = (isset($ilance->GPC['buynow_qty_lot_fixed'])) ? intval($ilance->GPC['buynow_qty_lot_fixed']) : '0';
		$ilance->GPC['items_in_lot'] = ($ilance->GPC['buynow_qty_lot'] == '1') ? (isset($ilance->GPC['items_in_lot_fixed']) ? $ilance->GPC['items_in_lot_fixed'] : '1') : '1';
		$ilance->GPC['classified_price'] = '';
		$ilance->GPC['classified_phone'] = '';
	
		($apihook = $ilance->api('selling_dosubmit_fixed_end')) ? eval($apihook) : false;
	}
	else if ($ilance->GPC['filtered_auctiontype'] == 'classified')
	{
		// classified only
		$ilance->GPC['buynow'] = '0';
		$ilance->GPC['reserve_price'] = '0';
		$ilance->GPC['reserve'] = '0';
		$ilance->GPC['buynow_price'] = '0';
		$ilance->GPC['buynow_qty'] = '1';
		$ilance->GPC['startprice'] = (isset($ilance->GPC['classified_price']) AND $ilance->GPC['classified_price'] > 0) ? sprintf("%01.2f", $ilance->currency->string_to_number($ilance->GPC['classified_price'])) : '';
		$ilance->GPC['buynow_qty_lot'] = '0';
		$ilance->GPC['items_in_lot'] = '1';
		$ilance->GPC['classified_price'] = (isset($ilance->GPC['classified_price']) AND $ilance->GPC['classified_price'] > 0) ? sprintf("%01.2f", $ilance->currency->string_to_number($ilance->GPC['classified_price'])) : '';
		$ilance->GPC['classified_phone'] = (isset($ilance->GPC['classified_phone']) AND !empty($ilance->GPC['classified_phone'])) ? handle_input_keywords($ilance->GPC['classified_phone']) : '';
	
		($apihook = $ilance->api('selling_dosubmit_classified_end')) ? eval($apihook) : false;
	}
	else
	{
		$ilance->GPC['buynow_qty'] = (isset($ilance->GPC['buynow_qty'])) ? intval($ilance->GPC['buynow_qty']) : '1';
		$ilance->GPC['buynow'] = $ilance->GPC['reserve_price'] = $ilance->GPC['reserve'] = $ilance->GPC['buynow_price'] = $ilance->GPC['startprice'] = '0';
		$ilance->GPC['buynow_qty_lot'] = (isset($ilance->GPC['buynow_qty_lot'])) ? intval($ilance->GPC['buynow_qty_lot']) : '0';
		$ilance->GPC['items_in_lot'] = ($ilance->GPC['buynow_qty_lot'] == '1') ? (isset($ilance->GPC['items_in_lot_fixed']) ? $ilance->GPC['items_in_lot_fixed'] : '1') : '1';
		$ilance->GPC['classified_price'] = '';
		$ilance->GPC['classified_phone'] = '';
	
		($apihook = $ilance->api('selling_dosubmit_other_end')) ? eval($apihook) : false;
	}
	unset($ilance->GPC['buynow_qty_fixed'], $ilance->GPC['buynow_price_fixed']);
	$ilance->GPC['filter_rating'] = isset($ilance->GPC['filter_rating']) ? intval($ilance->GPC['filter_rating']) : '0';
	$ilance->GPC['filtered_rating'] = isset($ilance->GPC['filtered_rating']) ? $ilance->GPC['filtered_rating'] : '';
	$ilance->GPC['filter_country'] = isset($ilance->GPC['filter_country']) ? intval($ilance->GPC['filter_country']) : '0';
	$ilance->GPC['filtered_country'] = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : '';
	$ilance->GPC['filter_state'] = isset($ilance->GPC['filter_state']) ? intval($ilance->GPC['filter_state']) : '0';
	$ilance->GPC['filtered_state'] = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : '';
	$ilance->GPC['filter_city'] = isset($ilance->GPC['filter_city']) ? intval($ilance->GPC['filter_city']) : '0';
	$ilance->GPC['filtered_city'] = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : '';
	$ilance->GPC['filter_zip'] = isset($ilance->GPC['filter_zip']) ? intval($ilance->GPC['filter_zip']) : '0';
	$ilance->GPC['filtered_zip'] = isset($ilance->GPC['filtered_zip']) ? $ilance->GPC['filtered_zip'] : '';
	$ilance->GPC['filter_underage'] = isset($ilance->GPC['filter_underage']) ? intval($ilance->GPC['filter_underage']) : '0';
	$ilance->GPC['filter_businessnumber'] = isset($ilance->GPC['filter_businessnumber']) ? $ilance->GPC['filter_businessnumber'] : '0';
	$ilance->GPC['filter_publicboard'] = isset($ilance->GPC['filter_publicboard']) ? intval($ilance->GPC['filter_publicboard']) : '0';
	$ilance->GPC['filtered_auctiontype'] = isset($ilance->GPC['filtered_auctiontype']) ? $ilance->GPC['filtered_auctiontype'] : 'regular';
	$ilance->GPC['filter_escrow'] = isset($ilance->GPC['filter_escrow']) ? intval($ilance->GPC['filter_escrow']) : '0';
	$ilance->GPC['filter_gateway'] = isset($ilance->GPC['filter_gateway']) ? intval($ilance->GPC['filter_gateway']) : '0';
	$ilance->GPC['filter_ccgateway'] = isset($ilance->GPC['filter_ccgateway']) ? intval($ilance->GPC['filter_ccgateway']) : '0';
	$ilance->GPC['filter_offline'] = isset($ilance->GPC['filter_offline']) ? intval($ilance->GPC['filter_offline']) : '0';
	$ilance->GPC['paymethod'] = (isset($ilance->GPC['paymethod']) AND $ilance->GPC['filter_offline'] != '0') ? $ilance->GPC['paymethod'] : array ();
	$ilance->GPC['paymethodcc'] = (isset($ilance->GPC['paymethodcc']) AND $ilance->GPC['filter_ccgateway'] != '0') ? $ilance->GPC['paymethodcc'] : array ();
	$ilance->GPC['paymethodoptions'] = (isset($ilance->GPC['paymethodoptions']) AND $ilance->GPC['filter_gateway'] != '0') ? $ilance->GPC['paymethodoptions'] : array ();
	$ilance->GPC['paymethodoptionsemail'] = (isset($ilance->GPC['paymethodoptionsemail']) AND $ilance->GPC['filter_gateway'] != '0') ? $ilance->GPC['paymethodoptionsemail'] : array ();
	// ### OTHER DETAILS ###########################################
	$ilance->GPC['project_type'] = 'forward';
	$ilance->GPC['project_state'] = 'product';
	$ilance->GPC['additional_info'] = isset($ilance->GPC['additional_info']) ? $ilance->GPC['additional_info'] : '';
	$ilance->GPC['description_videourl'] = isset($ilance->GPC['description_videourl']) ? strip_tags($ilance->GPC['description_videourl']) : '';
	$ilance->GPC['keywords'] = isset($ilance->GPC['keywords']) ? $ilance->GPC['keywords'] : '';
	$ilance->GPC['sku'] = isset($ilance->GPC['sku']) ? $ilance->GPC['sku'] : '';
	$ilance->GPC['upc'] = isset($ilance->GPC['upc']) ? $ilance->GPC['upc'] : '';
	$ilance->GPC['ean'] = isset($ilance->GPC['ean']) ? $ilance->GPC['ean'] : '';
	$ilance->GPC['partnumber'] = isset($ilance->GPC['partnumber']) ? $ilance->GPC['partnumber'] : '';
	$ilance->GPC['modelnumber'] = isset($ilance->GPC['modelnumber']) ? $ilance->GPC['modelnumber'] : '';
	$ilance->GPC['status'] = 'open';
	$ilance->GPC['bid_details'] = 'open';
	// #### RETURN POLICIES ########################################
	$ilance->GPC['returnaccepted'] = isset($ilance->GPC['returnaccepted']) ? intval($ilance->GPC['returnaccepted']) : '0';
	$ilance->GPC['returnwithin'] = isset($ilance->GPC['returnwithin']) ? intval($ilance->GPC['returnwithin']) : '0';
	$ilance->GPC['returngivenas'] = isset($ilance->GPC['returngivenas']) ? $ilance->GPC['returngivenas'] : 'none';
	$ilance->GPC['returnshippaidby'] = isset($ilance->GPC['returnshippaidby']) ? $ilance->GPC['returnshippaidby'] : 'none';
	$ilance->GPC['returnpolicy'] = isset($ilance->GPC['returnpolicy']) ? $ilance->GPC['returnpolicy'] : '';
	// #### SAVE AS DRAFT ##########################################                        
	if (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft'])
	{
		$ilance->GPC['draft'] = '1';
		$ilance->GPC['status'] = 'draft';
	}
	else
	{
		$ilance->GPC['draft'] = '0';
	}
	// #### CUSTOM INFORMATION #####################################
	$ilance->GPC['custom'] = (!empty($ilance->GPC['custom']) ? $ilance->GPC['custom'] : array ());
	$ilance->GPC['profileanswer'] = (!empty($ilance->GPC['profileanswer']) ? $ilance->GPC['profileanswer'] : array ());
	$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array ());
	$ilance->GPC['invitelist'] = isset($ilance->GPC['invitelist']) ? $ilance->GPC['invitelist'] : array ();
	$ilance->GPC['invitemessage'] = isset($ilance->GPC['invitemessage']) ? $ilance->GPC['invitemessage'] : '';
	// #### SCHEDULED AUCTION ONLY #################################
	$ilance->GPC['year'] = (isset($ilance->GPC['year'])) ? $ilance->GPC['year'] : '';
	$ilance->GPC['month'] = (isset($ilance->GPC['month'])) ? $ilance->GPC['month'] : '';
	$ilance->GPC['day'] = (isset($ilance->GPC['day'])) ? $ilance->GPC['day'] : '';
	$ilance->GPC['hour'] = (isset($ilance->GPC['hour'])) ? $ilance->GPC['hour'] : '';
	$ilance->GPC['min'] = (isset($ilance->GPC['min'])) ? $ilance->GPC['min'] : '';
	$ilance->GPC['sec'] = (isset($ilance->GPC['sec'])) ? $ilance->GPC['sec'] : '';
	// #### SHIPPING INFORMATION ###################################
	$shipping1 = array (
		'ship_method' => (isset($ilance->GPC['ship_method'])) ? $ilance->GPC['ship_method'] : 'flatrate',
		'ship_length' => (isset($ilance->GPC['ship_length'])) ? $ilance->GPC['ship_length'] : '12',
		'ship_width' => (isset($ilance->GPC['ship_width'])) ? $ilance->GPC['ship_width'] : '12',
		'ship_height' => (isset($ilance->GPC['ship_height'])) ? $ilance->GPC['ship_height'] : '12',
		'ship_weightlbs' => (isset($ilance->GPC['ship_weightlbs'])) ? $ilance->GPC['ship_weightlbs'] : '1',
		'ship_weightoz' => (isset($ilance->GPC['ship_weightoz'])) ? $ilance->GPC['ship_weightoz'] : '0',
		'ship_handlingtime' => (isset($ilance->GPC['ship_handlingtime'])) ? $ilance->GPC['ship_handlingtime'] : '3',
		'ship_handlingfee' => (isset($ilance->GPC['ship_handlingfee'])) ? $ilance->currency->string_to_number($ilance->GPC['ship_handlingfee']) : '0.00'
	);
	for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
	{
		$shipping2['ship_options_' . $i] = (isset($ilance->GPC['ship_options_' . $i])) ? $ilance->GPC['ship_options_' . $i] : '';
		$shipping2['ship_service_' . $i] = (isset($ilance->GPC['ship_service_' . $i])) ? intval($ilance->GPC['ship_service_' . $i]) : '';
		$shipping2['ship_packagetype_' . $i] = (isset($ilance->GPC['ship_packagetype_' . $i])) ? $ilance->GPC['ship_packagetype_' . $i] : '';
		$shipping2['ship_pickuptype_' . $i] = (isset($ilance->GPC['ship_pickuptype_' . $i])) ? $ilance->GPC['ship_pickuptype_' . $i] : '';
		$shipping2['ship_fee_' . $i] = (isset($ilance->GPC['ship_fee_' . $i])) ? $ilance->currency->string_to_number($ilance->GPC['ship_fee_' . $i]) : '0.00';
		$shipping2['ship_fee_next_' . $i] = (isset($ilance->GPC['ship_fee_next_' . $i])) ? $ilance->currency->string_to_number($ilance->GPC['ship_fee_next_' . $i]) : '0.00';
		$shipping2['freeshipping_' . $i] = (isset($ilance->GPC['freeshipping_' . $i])) ? intval($ilance->GPC['freeshipping_' . $i]) : '0';
		$shipping2['ship_options_custom_region_' . $i] = (isset($ilance->GPC['ship_options_custom_region_' . $i])) ? $ilance->GPC['ship_options_custom_region_' . $i] : array();
	}
	$ilance->GPC['shipping'] = array_merge($shipping1, $shipping2);
	unset($shipping1, $shipping2);
	// #### item location ##########################################
	$ilance->GPC['city'] = (isset($ilance->GPC['city'])) ? $ilance->GPC['city'] : $_SESSION['ilancedata']['user']['city'];
	$ilance->GPC['state'] = (isset($ilance->GPC['state'])) ? $ilance->GPC['state'] : $_SESSION['ilancedata']['user']['state'];
	$ilance->GPC['zipcode'] = (isset($ilance->GPC['zipcode'])) ? $ilance->GPC['zipcode'] : $_SESSION['ilancedata']['user']['postalzip'];
	$ilance->GPC['country'] = (isset($ilance->GPC['country'])) ? $ilance->GPC['country'] : $_SESSION['ilancedata']['user']['country'];
	// #### currency ###############################################
	$ilance->GPC['currencyid'] = (isset($ilance->GPC['currencyid'])) ? intval($ilance->GPC['currencyid']) : $ilconfig['globalserverlocale_defaultcurrency'];
	// #### tax information ########################################
	$ilance->GPC['salestaxstate'] = (isset($ilance->GPC['salestaxstate'])) ? $ilance->GPC['salestaxstate'] : '';
	$ilance->GPC['salestaxrate'] = (isset($ilance->GPC['salestaxrate'])) ? $ilance->GPC['salestaxrate'] : '0';
	$ilance->GPC['salestaxshipping'] = (isset($ilance->GPC['salestaxshipping'])) ? $ilance->GPC['salestaxshipping'] : '0';
	$apihookcustom = array();
	log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['selling'], $ilance->GPC['cmd']);
    
	($apihook = $ilance->api('selling_submit_end')) ? eval($apihook) : false;
    
	// #### CREATE AUCTION #########################################
	$ilance->auction_rfp->insert_product_auction(
		$_SESSION['ilancedata']['user']['userid'], $ilance->GPC['project_type'], $ilance->GPC['status'], $ilance->GPC['project_state'], $ilance->GPC['cid'], $ilance->GPC['rfpid'], $ilance->GPC['project_title'], $ilance->GPC['description'], $ilance->GPC['description_videourl'], $ilance->GPC['additional_info'], $ilance->GPC['keywords'], $ilance->GPC['custom'], $ilance->GPC['profileanswer'], $ilance->GPC['filtered_auctiontype'], $ilance->GPC['startprice'], $ilance->GPC['project_details'], $ilance->GPC['bid_details'], $ilance->GPC['filter_rating'], $ilance->GPC['filter_country'], $ilance->GPC['filter_state'], $ilance->GPC['filter_city'], $ilance->GPC['filter_zip'], $ilance->GPC['filter_businessnumber'], $ilance->GPC['filtered_rating'], $ilance->GPC['filtered_country'], $ilance->GPC['filtered_state'], $ilance->GPC['filtered_city'], $ilance->GPC['filtered_zip'], $ilance->GPC['city'], $ilance->GPC['state'], $ilance->GPC['zipcode'], $ilance->GPC['country'], $ilance->GPC['shipping'], $ilance->GPC['buynow'], $ilance->GPC['buynow_price'], $ilance->GPC['buynow_qty'], $ilance->GPC['buynow_qty_lot'], $ilance->GPC['items_in_lot'], $ilance->GPC['enhancements'], $ilance->GPC['reserve'], $ilance->GPC['reserve_price'], $ilance->GPC['filter_underage'], $ilance->GPC['filter_escrow'], $ilance->GPC['filter_gateway'], $ilance->GPC['filter_ccgateway'], $ilance->GPC['filter_offline'], $ilance->GPC['filter_publicboard'], $ilance->GPC['invitelist'], $ilance->GPC['invitemessage'], $ilance->GPC['year'], $ilance->GPC['month'], $ilance->GPC['day'], $ilance->GPC['hour'], $ilance->GPC['min'], $ilance->GPC['sec'], $ilance->GPC['duration'], $ilance->GPC['duration_unit'], $ilance->GPC['paymethod'], $ilance->GPC['paymethodcc'], $ilance->GPC['paymethodoptions'], $ilance->GPC['paymethodoptionsemail'], $ilance->GPC['draft'], $ilance->GPC['returnaccepted'], $ilance->GPC['returnwithin'], $ilance->GPC['returngivenas'], $ilance->GPC['returnshippaidby'], $ilance->GPC['returnpolicy'], $ilance->GPC['donation'], $ilance->GPC['charityid'], $ilance->GPC['donationpercentage'], $skipemailprocess = 0, $apihookcustom, $isbulkupload = false, $sample = '', $ilance->GPC['currencyid'], $ilance->GPC['classified_price'], $ilance->GPC['classified_phone'], $ilance->GPC['sku'], $ilance->GPC['upc'], $ilance->GPC['ean'], $ilance->GPC['partnumber'], $ilance->GPC['modelnumber'], $ilance->GPC['salestaxstate'], $ilance->GPC['salestaxrate'], $ilance->GPC['salestaxshipping']
	);
    
	($apihook = $ilance->api('selling_submit_post_end')) ? eval($apihook) : false;
    
	exit();
}
// #### SAVE EXISTING PRODUCT AUCTION ##################################
else if (isset($ilance->GPC['dosave']))
{
	$ownerid = (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin']) ? fetch_auction('user_id', intval($ilance->GPC['rfpid'])) : $_SESSION['ilancedata']['user']['userid'];
	// #### final category checkup #################################
	if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $ilance->GPC['cid']) == false)
	{
		print_notice('{_this_is_a_nonposting_category}', '{_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings}', 'javascript:history.back(1);', '{_back}');
		exit();
	}
	if (empty($ilance->GPC['rfpid']) OR empty($ilance->GPC['description']) OR empty($ilance->GPC['date_end']) OR !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})", $ilance->GPC['date_end']))
	{
		$area_title = '{_rfp_details_update_error}';
		$page_title = SITE_NAME . ' - {_rfp_details_update_error}';
		print_notice('{_rfp_was_not_updated}', '<p>{_were_sorry_there_was_a_problem_updating_your_request_for_proposal}</p><ul><li />{_description_can_not_be_empty}<li />{_verify_the_end_date_for_your_rfp_is_formatted_correctly}</ul><p>{_please_contact_customer_support}</p>', 'javascript:history.back(1);', '{_retry}');
		exit();
	}
	$ilance->GPC['filtered_auctiontype'] = isset($ilance->GPC['filtered_auctiontype']) ? $ilance->GPC['filtered_auctiontype'] : $ilance->GPC['old']['filtered_auctiontype'];
	$sql_bids = $ilance->db->query("SELECT bids FROM " . DB_PREFIX . "projects WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'");
	$res_bids = $ilance->db->fetch_array($sql_bids);
	$ilance->GPC['old']['bids'] = $res_bids['bids'];
	if (isset($ilance->GPC['old']['bids']) AND $ilance->GPC['old']['bids'] > 0)
	{
		if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] AND isset($ilance->GPC['admincp']) AND $ilance->GPC['admincp'])
		{
			// this is so admin can update all fields of the listing
			$show['bidsplaced'] = false;
		}
		else
		{
			$show['bidsplaced'] = true;
		}
	}
	else
	{
	    $show['bidsplaced'] = false;
	}
    
	$ilance->GPC['attachmentlist'] = '';
	$ilance->GPC['attachmentsfilesize'] = 0;
	$sql_attach = $ilance->db->query("
		SELECT filename, filesize
		FROM " . DB_PREFIX . "attachment
		WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
			AND user_id = '" . intval($ownerid) . "'
			AND (attachtype = 'itemphoto' OR attachtype = 'slideshow') 
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_attach) > 0)
	{
		//$ilance->GPC['attachmentlist'] .= '{_attachments}: ';
		while ($res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC))
		{
			$ilance->GPC['attachmentlist'] .= $res_attach['filename'] . ',<br /> ';
			$ilance->GPC['attachmentsfilesize'] += $res_attach['filesize'];
		}
	}
	// #### HANDLE AUCTION LISTING ENHANCEMENTS ############
	// this will attempt to debit the acocunt of the users account balance if possible
	$ilance->GPC['featured'] = (isset($ilance->GPC['old']['featured']) ? $ilance->GPC['old']['featured'] : '0');
	$ilance->GPC['featured_searchresults'] = (isset($ilance->GPC['old']['featured_searchresults']) ? $ilance->GPC['old']['featured_searchresults'] : '0');
	$ilance->GPC['highlite'] = (isset($ilance->GPC['old']['highlite']) ? $ilance->GPC['old']['highlite'] : '0');
	$ilance->GPC['bold'] = (isset($ilance->GPC['old']['bold']) ? $ilance->GPC['old']['bold'] : '0');
	$ilance->GPC['autorelist'] = (isset($ilance->GPC['old']['autorelist']) ? $ilance->GPC['old']['autorelist'] : '0');
	$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array ());
	if (is_array($ilance->GPC['enhancements']))
	{
		$enhance = $ilance->auction_fee->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['rfpid']), 'update', 'product');
		if (is_array($enhance))
		{
			$ilance->GPC['featured'] = (int) $enhance['featured'];
			$ilance->GPC['featured_searchresults'] = (int) $enhance['featured_searchresults'];
			$ilance->GPC['highlite'] = (int) $enhance['highlite'];
			$ilance->GPC['bold'] = (int) $enhance['bold'];
			$ilance->GPC['autorelist'] = (int) $enhance['autorelist'];
		}
	}
	$ilance->GPC['featured_date'] = ($ilance->GPC['featured'] AND isset($ilance->GPC['old']['featured_date']) AND $ilance->GPC['old']['featured_date'] == '0000-00-00 00:00:00') ? DATETIME24H : '0000-00-00 00:00:00';
    
	// #### does owner extend the auction? #################
	$sqlextend = ((isset($ilance->GPC['extend']) AND $ilance->GPC['extend'] > 0 AND fetch_auction('status', $ilance->GPC['rfpid']) == 'open' AND strtotime($ilance->GPC['date_end']) > TIMENOW) ? "date_end = DATE_ADD(date_end, INTERVAL " . intval($ilance->GPC['extend']) . " DAY)," : '');
	$sqltitle = '';
	if ($show['bidsplaced'] == false AND $ilconfig['globalfilters_changeauctiontitle'] == '1' OR $_SESSION['ilancedata']['user']['isadmin'])
	{
		if (isset($ilance->GPC['project_title']) AND !empty($ilance->GPC['project_title']))
		{
			$ilance->GPC['project_title'] = strip_tags($ilance->GPC['project_title']);
			$sqltitle = "project_title = '" . $ilance->db->escape_string($ilance->GPC['project_title']) . "',";
		}
		else
		{
			$ilance->GPC['project_title'] = (isset($ilance->GPC['old']['project_title']) AND !empty($ilance->GPC['old']['project_title'])) ? $ilance->GPC['old']['project_title'] : '';
		}
	}
	$ilance->GPC['filter_rating'] = isset($ilance->GPC['filter_rating']) ? $ilance->GPC['filter_rating'] : '0';
	$ilance->GPC['filter_country'] = isset($ilance->GPC['filter_country']) ? $ilance->GPC['filter_country'] : '0';
	$ilance->GPC['filter_state'] = isset($ilance->GPC['filter_state']) ? $ilance->GPC['filter_state'] : '0';
	$ilance->GPC['filter_city'] = isset($ilance->GPC['filter_city']) ? $ilance->GPC['filter_city'] : '0';
	$ilance->GPC['filter_zip'] = isset($ilance->GPC['filter_zip']) ? $ilance->GPC['filter_zip'] : '0';
	$ilance->GPC['filter_underage'] = isset($ilance->GPC['filter_underage']) ? $ilance->GPC['filter_underage'] : '0';
	$ilance->GPC['filter_businessnumber'] = isset($ilance->GPC['filter_businessnumber']) ? $ilance->GPC['filter_businessnumber'] : '0';
	$ilance->GPC['filter_publicboard'] = isset($ilance->GPC['filter_publicboard']) ? $ilance->GPC['filter_publicboard'] : $ilance->GPC['old']['filter_publicboard'];
	$ilance->GPC['filter_bidtype'] = isset($ilance->GPC['filter_bidtype']) ? $ilance->GPC['filter_bidtype'] : $ilance->GPC['old']['filter_bidtype'];
	$ilance->GPC['filter_budget'] = isset($ilance->GPC['filter_budget']) ? $ilance->GPC['filter_budget'] : $ilance->GPC['old']['filter_budget'];
	$ilance->GPC['filter_escrow'] = isset($ilance->GPC['filter_escrow']) ? $ilance->GPC['filter_escrow'] : '0';
	$ilance->GPC['filter_gateway'] = isset($ilance->GPC['filter_gateway']) ? $ilance->GPC['filter_gateway'] : '0';
	$ilance->GPC['filter_ccgateway'] = isset($ilance->GPC['filter_ccgateway']) ? $ilance->GPC['filter_ccgateway'] : '0';
	$ilance->GPC['filter_offline'] = isset($ilance->GPC['filter_offline']) ? $ilance->GPC['filter_offline'] : '0';
	$ilance->GPC['filtered_rating'] = isset($ilance->GPC['filtered_rating']) ? $ilance->GPC['filtered_rating'] : (isset($ilance->GPC['old']['filtered_rating']) ? $ilance->GPC['old']['filtered_rating'] : '');
	$ilance->GPC['filtered_country'] = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : (isset($ilance->GPC['old']['filtered_country']) ? $ilance->GPC['old']['filtered_country'] : '');
	$ilance->GPC['filtered_state'] = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : (isset($ilance->GPC['old']['filtered_state']) ? $ilance->GPC['old']['filtered_state'] : '');
	$ilance->GPC['filtered_city'] = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : (isset($ilance->GPC['old']['filtered_city']) ? $ilance->GPC['old']['filtered_city'] : '');
	$ilance->GPC['filtered_zip'] = isset($ilance->GPC['filtered_zip']) ? format_zipcode($ilance->GPC['filtered_zip']) : (isset($ilance->GPC['old']['filtered_zip']) ? format_zipcode($ilance->GPC['old']['filtered_zip']) : '');
	$ilance->GPC['filtered_bidtype'] = isset($ilance->GPC['filtered_bidtype']) ? $ilance->GPC['filtered_bidtype'] : $ilance->GPC['old']['filtered_bidtype'];
	$ilance->GPC['filtered_budgetid'] = isset($ilance->GPC['filtered_budgetid']) ? $ilance->GPC['filtered_budgetid'] : $ilance->GPC['old']['filtered_budgetid'];
	$ilance->GPC['additional_info'] = isset($ilance->GPC['additional_info']) ? $ilance->GPC['additional_info'] : (isset($ilance->GPC['old']['additional_info']) ? $ilance->GPC['old']['additional_info'] : '');
	$ilance->GPC['keywords'] = isset($ilance->GPC['keywords']) ? strip_tags($ilance->GPC['keywords']) : (isset($ilance->GPC['old']['keywords']) ? strip_tags($ilance->GPC['old']['keywords']) : '');
	$ilance->GPC['sku'] = isset($ilance->GPC['sku']) ? $ilance->GPC['sku'] : (isset($ilance->GPC['old']['sku']) ? strip_tags($ilance->GPC['old']['sku']) : '');
	$ilance->GPC['upc'] = isset($ilance->GPC['upc']) ? $ilance->GPC['upc'] : (isset($ilance->GPC['old']['upc']) ? strip_tags($ilance->GPC['old']['upc']) : '');
	$ilance->GPC['ean'] = isset($ilance->GPC['ean']) ? $ilance->GPC['ean'] : (isset($ilance->GPC['old']['ean']) ? strip_tags($ilance->GPC['old']['ean']) : '');
	$ilance->GPC['partnumber'] = isset($ilance->GPC['partnumber']) ? $ilance->GPC['partnumber'] : (isset($ilance->GPC['old']['partnumber']) ? strip_tags($ilance->GPC['old']['partnumber']) : '');
	$ilance->GPC['modelnumber'] = isset($ilance->GPC['modelnumber']) ? $ilance->GPC['modelnumber'] : (isset($ilance->GPC['old']['modelnumber']) ? strip_tags($ilance->GPC['old']['modelnumber']) : '');
	$ilance->GPC['project_details'] = isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : $ilance->GPC['old']['project_details'];
	$ilance->GPC['invitelist'] = isset($ilance->GPC['invitelist']) ? $ilance->GPC['invitelist'] : array ();
	$ilance->GPC['invitemessage'] = isset($ilance->GPC['invitemessage']) ? $ilance->GPC['invitemessage'] : '';
	if ($ilance->GPC['filtered_auctiontype'] == 'fixed')
	{
		$ilance->GPC['startprice'] = $ilance->GPC['buynow_price_fixed'];
	}
	$ilance->GPC['startprice'] = isset($ilance->GPC['startprice']) ? $ilance->GPC['startprice'] : $ilance->GPC['old']['startprice'];
	$ilance->GPC['bid_details'] = isset($ilance->GPC['bid_details']) ? $ilance->GPC['bid_details'] : $ilance->GPC['old']['bid_details'];
	$ilance->GPC['buynow'] = isset($ilance->GPC['buynow']) ? intval($ilance->GPC['buynow']) : $ilance->GPC['old']['buynow'];
	$ilance->GPC['description'] = isset($ilance->GPC['description']) ? $ilance->GPC['description'] : (isset($ilance->GPC['old']['description']) ? $ilance->GPC['old']['description'] : '');
	$ilance->GPC['description_videourl'] = isset($ilance->GPC['description_videourl']) ? strip_tags($ilance->GPC['description_videourl']) : (isset($ilance->GPC['old']['description_videourl']) ? $ilance->GPC['old']['description_videourl'] : '');
	$ilance->GPC['currencyid'] = isset($ilance->GPC['currencyid']) ? $ilance->GPC['currencyid'] : $ilance->GPC['old']['currencyid'];
	if (!$show['bidsplaced'] AND $ilance->GPC['startprice'] != $ilance->GPC['old']['startprice'])
	{
		$sqlextend .= "currentprice = '" . $ilance->db->escape_string($ilance->GPC['startprice']) . "',";
	}
	$ilance->GPC['buynow_qty'] = isset($ilance->GPC['buynow_qty']) ? intval($ilance->GPC['buynow_qty']) : $ilance->GPC['old']['buynow_qty'];
	$ilance->GPC['buynow_qty'] = (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'fixed' AND !empty($ilance->GPC['buynow_qty_fixed'])) ? intval($ilance->GPC['buynow_qty_fixed']) : 1;
	if (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'fixed')
	{
		if (!empty($ilance->GPC['buynow_price_fixed']))
		{
			$ilance->GPC['buynow_price'] = $ilance->GPC['buynow_price_fixed'];
		}
		$ilance->GPC['items_in_lot'] = isset($ilance->GPC['items_in_lot_fixed']) ? intval($ilance->GPC['items_in_lot_fixed']) : (isset($ilance->GPC['old']['items_in_lot']) ? $ilance->GPC['old']['items_in_lot'] : '');
		$ilance->GPC['buynow_qty_lot'] = isset($ilance->GPC['buynow_qty_lot_fixed']) ? intval($ilance->GPC['buynow_qty_lot_fixed']) : (isset($ilance->GPC['old']['buynow_qty_lot']) ? $ilance->GPC['old']['buynow_qty_lot'] : '0');
		$ilance->GPC['buynow_price'] = (isset($ilance->GPC['buynow_price']) AND $ilance->GPC['buynow_price'] > 0) ? $ilance->GPC['buynow_price'] : (isset($ilance->GPC['old']['buynow_price']) ? $ilance->GPC['old']['buynow_price'] : '0.00');
	}
	else if (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'regular')
	{
		$ilance->GPC['items_in_lot'] = isset($ilance->GPC['items_in_lot_regular']) ? intval($ilance->GPC['items_in_lot_regular']) : (isset($ilance->GPC['old']['items_in_lot']) ? $ilance->GPC['old']['items_in_lot'] : '');
		$ilance->GPC['buynow_qty_lot'] = isset($ilance->GPC['buynow_qty_lot_regular']) ? intval($ilance->GPC['buynow_qty_lot_regular']) : (isset($ilance->GPC['old']['buynow_qty_lot']) ? $ilance->GPC['old']['buynow_qty_lot'] : '0');
		$ilance->GPC['buynow_price'] = (isset($ilance->GPC['buynow_price'])) ? $ilance->GPC['buynow_price'] : (isset($ilance->GPC['old']['buynow_price']) ? $ilance->GPC['old']['buynow_price'] : '0.00');
	}
	else
	{
		$ilance->GPC['items_in_lot'] = '';
		$ilance->GPC['buynow_qty_lot'] = '0';
		$ilance->GPC['buynow_price'] = (isset($ilance->GPC['buynow_price']) AND $ilance->GPC['buynow_price'] > 0) ? $ilance->GPC['buynow_price'] : (isset($ilance->GPC['old']['buynow_price']) ? $ilance->GPC['old']['buynow_price'] : '0.00');
	}
	$ilance->GPC['buynow'] = (isset($ilance->GPC['buynow_price']) AND $ilance->GPC['buynow_price'] > 0) ? '1' : '0';
	$ilance->GPC['reserve_price'] = isset($ilance->GPC['reserve_price']) ? $ilance->GPC['reserve_price'] : $ilance->GPC['old']['reserve_price'];
	$ilance->GPC['reserve'] = $ilance->GPC['reserve_price'] > 0 ? '1' : '0';
	// #### CLASSIFIED AD ##########################################
	if ($ilconfig['enableclassifiedtab'])
	{
		if (isset($ilance->GPC['filtered_auctiontype']) AND ($ilance->GPC['filtered_auctiontype'] == 'classified'))
		{
			$ilance->GPC['classified_phone'] = isset($ilance->GPC['classified_phone']) ? $ilance->GPC['classified_phone'] : $ilance->GPC['old']['classified_phone'];
			$ilance->GPC['classified_price'] = isset($ilance->GPC['classified_price']) ? $ilance->GPC['classified_price'] : $ilance->GPC['old']['classified_price'];
		}
		else
		{
			$ilance->GPC['classified_phone'] = '';
			$ilance->GPC['classified_price'] = '';
		}
	}
	else
	{
		$ilance->GPC['classified_phone'] = '';
		$ilance->GPC['classified_price'] = '';
	}
	// #### RETURN POLICIES ########################################
	$ilance->GPC['returnaccepted'] = isset($ilance->GPC['returnaccepted']) ? intval($ilance->GPC['returnaccepted']) : $ilance->GPC['old']['returnaccepted'];
	$ilance->GPC['returnwithin'] = isset($ilance->GPC['returnwithin']) ? intval($ilance->GPC['returnwithin']) : $ilance->GPC['old']['returnwithin'];
	$ilance->GPC['returngivenas'] = isset($ilance->GPC['returngivenas']) ? $ilance->GPC['returngivenas'] : (isset($ilance->GPC['old']['returngivenas']) ? $ilance->GPC['old']['returngivenas'] : '');
	$ilance->GPC['returnshippaidby'] = isset($ilance->GPC['returnshippaidby']) ? $ilance->GPC['returnshippaidby'] : (isset($ilance->GPC['old']['returnshippaidby']) ? $ilance->GPC['old']['returnshippaidby'] : '');
	$ilance->GPC['returnpolicy'] = isset($ilance->GPC['returnpolicy']) ? $ilance->GPC['returnpolicy'] : (isset($ilance->GPC['old']['returnpolicy']) ? $ilance->GPC['old']['returnpolicy'] : '');
	if ($ilance->GPC['returnaccepted'] == '0')
	{
		$ilance->GPC['returnwithin'] = '0';
		$ilance->GPC['returngivenas'] = 'none';
		$ilance->GPC['returnpolicy'] = '';
	}
	// #### DONATION DETAILS ###############################
	$ilance->GPC['donation'] = isset($ilance->GPC['donation']) ? intval($ilance->GPC['donation']) : 0;
	$ilance->GPC['charityid'] = isset($ilance->GPC['charityid']) ? intval($ilance->GPC['charityid']) : 0;
	$ilance->GPC['donationpercentage'] = isset($ilance->GPC['donationpercentage']) ? intval($ilance->GPC['donationpercentage']) : 0;
	if ($ilance->GPC['donation'] == 0)
	{
		$ilance->GPC['charityid'] = $ilance->GPC['donationpercentage'] = 0;
	}
	// #### SELLER DIRECT PAYMENT LOGIC ############
	$ilance->GPC['paymethodoptions'] = isset($ilance->GPC['paymethodoptions']) ? serialize($ilance->GPC['paymethodoptions']) : $ilance->GPC['old']['paymethodoptions'];
	$ilance->GPC['paymethodoptionsemail'] = isset($ilance->GPC['paymethodoptionsemail']) ? serialize($ilance->GPC['paymethodoptionsemail']) : $ilance->GPC['old']['paymethodoptionsemail'];
	$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? serialize($ilance->GPC['paymethod']) : $ilance->GPC['old']['paymethod'];
	$ilance->GPC['paymethod'] = empty($ilance->GPC['paymethod']) ? '' : $ilance->GPC['paymethod'];
	$ilance->GPC['paymethodcc'] = isset($ilance->GPC['paymethodcc']) ? serialize($ilance->GPC['paymethodcc']) : $ilance->GPC['old']['paymethodcc'];
	$ilance->GPC['paymethodcc'] = empty($ilance->GPC['paymethodcc']) ? '' : $ilance->GPC['paymethodcc'];
	// #### SHIPPING INFORMATION ###########################
	$shipping1 = array (
		'ship_method' => (isset($ilance->GPC['ship_method'])) ? $ilance->GPC['ship_method'] : 'flatrate',
		'ship_length' => (isset($ilance->GPC['ship_length'])) ? $ilance->GPC['ship_length'] : '12',
		'ship_width' => (isset($ilance->GPC['ship_width'])) ? $ilance->GPC['ship_width'] : '12',
		'ship_height' => (isset($ilance->GPC['ship_height'])) ? $ilance->GPC['ship_height'] : '12',
		'ship_weightlbs' => (isset($ilance->GPC['ship_weightlbs'])) ? $ilance->GPC['ship_weightlbs'] : '1',
		'ship_weightoz' => (isset($ilance->GPC['ship_weightoz'])) ? $ilance->GPC['ship_weightoz'] : '0',
		'ship_handlingtime' => (isset($ilance->GPC['ship_handlingtime'])) ? $ilance->GPC['ship_handlingtime'] : '3',
		'ship_handlingfee' => (isset($ilance->GPC['ship_handlingfee'])) ? $ilance->GPC['ship_handlingfee'] : '0.00'
	);
	for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
	{
		$shipping2['ship_options_' . $i] = (isset($ilance->GPC['ship_options_' . $i])) ? $ilance->GPC['ship_options_' . $i] : '';
		$shipping2['ship_service_' . $i] = (isset($ilance->GPC['ship_service_' . $i])) ? intval($ilance->GPC['ship_service_' . $i]) : (isset($ilance->GPC['old']['ship_service_' . $i]) ? $ilance->GPC['old']['ship_service_' . $i] : '');
		$shipping2['ship_packagetype_' . $i] = (isset($ilance->GPC['ship_packagetype_' . $i])) ? $ilance->GPC['ship_packagetype_' . $i] : (isset($ilance->GPC['old']['ship_packagetype_' . $i]) ? $ilance->GPC['old']['ship_packagetype_' . $i] : '');
		$shipping2['ship_pickuptype_' . $i] = (isset($ilance->GPC['ship_pickuptype_' . $i])) ? $ilance->GPC['ship_pickuptype_' . $i] : (isset($ilance->GPC['old']['ship_pickuptype_' . $i]) ? $ilance->GPC['old']['ship_pickuptype_' . $i] : '');
		$shipping2['ship_fee_' . $i] = (isset($ilance->GPC['ship_fee_' . $i])) ? $ilance->GPC['ship_fee_' . $i] : '0.00';
		$shipping2['ship_fee_next_' . $i] = (isset($ilance->GPC['ship_fee_next_' . $i])) ? $ilance->currency->string_to_number($ilance->GPC['ship_fee_next_' . $i]) : '0.00';
		$shipping2['freeshipping_' . $i] = (isset($ilance->GPC['freeshipping_' . $i])) ? intval($ilance->GPC['freeshipping_' . $i]) : '0';
		$shipping2['ship_options_custom_region_' . $i] = (isset($ilance->GPC['ship_options_custom_region_' . $i])) ? $ilance->GPC['ship_options_custom_region_' . $i] : array ();
	}
	$ilance->GPC['shipping'] = array_merge($shipping1, $shipping2);
	unset($shipping1, $shipping2);
	// #### item location ##########################################
	$ilance->GPC['city'] = (isset($ilance->GPC['city'])) ? $ilance->GPC['city'] : $ilance->GPC['old']['city'];
	$ilance->GPC['state'] = (isset($ilance->GPC['state'])) ? $ilance->GPC['state'] : $ilance->GPC['old']['state'];
	$ilance->GPC['zipcode'] = (isset($ilance->GPC['zipcode'])) ? $ilance->GPC['zipcode'] : $ilance->GPC['old']['zipcode'];
	$ilance->GPC['country'] = (isset($ilance->GPC['country'])) ? $ilance->GPC['country'] : $ilance->GPC['old']['country'];
	$ilance->GPC['countryid'] = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
    
	// #### tax information ########################################
	$ilance->GPC['salestaxstate'] = (isset($ilance->GPC['salestaxstate'])) ? $ilance->GPC['salestaxstate'] : '';
	$ilance->GPC['salestaxrate'] = (isset($ilance->GPC['salestaxrate'])) ? $ilance->GPC['salestaxrate'] : 0;
	$ilance->GPC['salestaxshipping'] = (isset($ilance->GPC['salestaxshipping'])) ? $ilance->GPC['salestaxshipping'] : 0;
	$sql = $ilance->db->query("
		SELECT cid, status, project_state
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
			AND user_id = '" . intval($ownerid) . "'
	", 0, null, __FILE__, __LINE__);
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['selling'], $ilance->GPC['cmd']);
	$visible = (($ilconfig['moderationsystem_disableauctionmoderation'] == '1' OR $res['status'] == 'draft') ? '1' : '0');
    
	($apihook = $ilance->api('update_auction_submit_start')) ? eval($apihook) : false;
    
	// handle listing differences and store into revision log	
	$ilance->auction_post->handle_revision_log_changes('product');
	if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
	{
		$ilance->auction_post->process_custom_questions($ilance->GPC['custom'], $ilance->GPC['rfpid'], 'product', 'update');
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects 
		SET $sqlextend
		cid = '" . intval($ilance->GPC['cid']) . "',
		visible = '" . $visible . "',
		$sqltitle
		description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
		description_videourl = '" . $ilance->db->escape_string($ilance->GPC['description_videourl']) . "',
		keywords = '" . $ilance->db->escape_string($ilance->GPC['keywords']) . "',
		sku = '" . $ilance->db->escape_string($ilance->GPC['sku']) . "',
		upc = '" . $ilance->db->escape_string($ilance->GPC['upc']) . "',
		ean = '" . $ilance->db->escape_string($ilance->GPC['ean']) . "',
		partnumber = '" . $ilance->db->escape_string($ilance->GPC['partnumber']) . "',
		modelnumber = '" . $ilance->db->escape_string($ilance->GPC['modelnumber']) . "',
		additional_info = '" . $ilance->db->escape_string($ilance->GPC['additional_info']) . "',
		startprice = '" . $ilance->db->escape_string($ilance->GPC['startprice']) . "',
		paymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
		paymethodcc = '" . $ilance->db->escape_string($ilance->GPC['paymethodcc']) . "',
		bid_details = '" . $ilance->db->escape_string($ilance->GPC['bid_details']) . "',
		project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "',
		buynow = '" . intval($ilance->GPC['buynow']) . "',
		buynow_price = '" . $ilance->db->escape_string($ilance->GPC['buynow_price']) . "',
		buynow_qty = '" . intval($ilance->GPC['buynow_qty']) . "',
		buynow_qty_lot = '" . intval($ilance->GPC['buynow_qty_lot']) . "',
		items_in_lot = '" . intval($ilance->GPC['items_in_lot']) . "',
		reserve = '" . intval($ilance->GPC['reserve']) . "',
		reserve_price = '" . $ilance->db->escape_string($ilance->GPC['reserve_price']) . "',
		returnaccepted = '" . intval($ilance->GPC['returnaccepted']) . "',
		returnwithin = '" . intval($ilance->GPC['returnwithin']) . "',
		returngivenas = '" . $ilance->db->escape_string($ilance->GPC['returngivenas']) . "',
		returnpolicy = '" . $ilance->db->escape_string($ilance->GPC['returnpolicy']) . "',
		filter_rating = '" . intval($ilance->GPC['filter_rating']) . "',
		filter_country = '" . intval($ilance->GPC['filter_country']) . "',
		filter_state = '" . intval($ilance->GPC['filter_state']) . "',
		filter_city = '" . intval($ilance->GPC['filter_city']) . "',
		filter_zip = '" . intval($ilance->GPC['filter_zip']) . "',
		filter_underage = '" . intval($ilance->GPC['filter_underage']) . "',
		filter_businessnumber = '" . intval($ilance->GPC['filter_businessnumber']) . "',
		filter_publicboard = '" . intval($ilance->GPC['filter_publicboard']) . "',
		filter_bidtype = '" . intval($ilance->GPC['filter_bidtype']) . "',
		filter_budget = '" . intval($ilance->GPC['filter_budget']) . "',
		filter_escrow = '" . intval($ilance->GPC['filter_escrow']) . "',
		filter_gateway = '" . intval($ilance->GPC['filter_gateway']) . "',
		filter_ccgateway = '" . intval($ilance->GPC['filter_ccgateway']) . "',
		filter_offline = '" . intval($ilance->GPC['filter_offline']) . "',
		filtered_rating = '" . $ilance->db->escape_string($ilance->GPC['filtered_rating']) . "',
		filtered_country = '" . $ilance->db->escape_string($ilance->GPC['filtered_country']) . "',
		filtered_state = '" . ucfirst($ilance->db->escape_string($ilance->GPC['filtered_state'])) . "',
		filtered_city = '" . ucfirst($ilance->db->escape_string($ilance->GPC['filtered_city'])) . "',
		filtered_zip = '" . mb_strtoupper($ilance->db->escape_string($ilance->GPC['filtered_zip'])) . "',
		filtered_bidtype = '" . mb_strtoupper($ilance->db->escape_string($ilance->GPC['filtered_bidtype'])) . "',
		filtered_budgetid = '" . intval($ilance->GPC['filtered_budgetid']) . "',
		filtered_auctiontype = '" . $ilance->db->escape_string($ilance->GPC['filtered_auctiontype']) . "',
		classified_phone = '" . $ilance->db->escape_string($ilance->GPC['classified_phone']) . "',
		classified_price = '" . $ilance->db->escape_string($ilance->GPC['classified_price']) . "',
		featured = '" . intval($ilance->GPC['featured']) . "',
		featured_date = '" . $ilance->db->escape_string($ilance->GPC['featured_date']) . "',
		featured_searchresults = '" . $ilance->db->escape_string($ilance->GPC['featured_searchresults']) . "',
		highlite = '" . intval($ilance->GPC['highlite']) . "',
		bold = '" . intval($ilance->GPC['bold']) . "',
		autorelist = '" . intval($ilance->GPC['autorelist']) . "',
		returnaccepted = '" . intval($ilance->GPC['returnaccepted']) . "',
		returnwithin = '" . intval($ilance->GPC['returnwithin']) . "',
		returngivenas = '" . $ilance->db->escape_string($ilance->GPC['returngivenas']) . "',
		returnshippaidby = '" . $ilance->db->escape_string($ilance->GPC['returnshippaidby']) . "',
		returnpolicy = '" . $ilance->db->escape_string($ilance->GPC['returnpolicy']) . "',
		donation = '" . intval($ilance->GPC['donation']) . "',
		charityid = '" . intval($ilance->GPC['charityid']) . "',
		donationpercentage = '" . intval($ilance->GPC['donationpercentage']) . "',
		paymethodoptions = '" . $ilance->db->escape_string($ilance->GPC['paymethodoptions']) . "',
		paymethodoptionsemail = '" . $ilance->db->escape_string($ilance->GPC['paymethodoptionsemail']) . "',
		currencyid = '" . intval($ilance->GPC['currencyid']) . "',
		countryid = '" . intval($ilance->GPC['countryid']) . "',
		country = '" . $ilance->db->escape_string($ilance->GPC['country']) . "',
		state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "',
		city = '" . $ilance->db->escape_string($ilance->GPC['city']) . "',
		zipcode = '" . $ilance->db->escape_string(format_zipcode($ilance->GPC['zipcode'])) . "',
		salestaxstate = '" . $ilance->db->escape_string($ilance->GPC['salestaxstate']) . "',
		salestaxrate = '" . $ilance->db->escape_string($ilance->GPC['salestaxrate']) . "',
		salestaxshipping = '" . intval($ilance->GPC['salestaxshipping']) . "'
		WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
			AND user_id = '" . intval($ownerid) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	// #### save shipping logic ####################
	if (!$show['bidsplaced'])
	{
		$ilance->shipping->save_item_shipping_logic($ilance->GPC['rfpid'], $ilance->GPC['shipping']);
	}
	if (empty($ilance->GPC['project_title']))
	{
		$ilance->GPC['project_title'] = fetch_auction('project_title', $ilance->GPC['rfpid']);
	}
	$ilance->auction_rfp->dispatch_external_members_email('product', $ilance->GPC['rfpid'], intval($ownerid), $ilance->GPC['project_title'], '', $ilance->GPC['date_end'], $ilance->GPC['invitelist'], $ilance->GPC['invitemessage'], '1', 'update');
	// #### determine if we need to move the category (user change)
	$ilance->categories->move_listing_category_from_to($ilance->GPC['rfpid'], $res['cid'], $ilance->GPC['cid'], $res['project_state'], $res['status'], $res['status']);
	unset($res);
    
	($apihook = $ilance->api('update_auction_submit')) ? eval($apihook) : false;
    
	$area_title = '{_rfp_detailed_information_updated}';
	$page_title = SITE_NAME . ' - {_rfp_detailed_information_updated}';
	if ($ilconfig['moderationsystem_disableauctionmoderation'] == '0')
	{
		$ilance->categories->build_category_count(intval($ilance->GPC['cid']), 'subtract', "seller updating his listings from selling activity: subtracting increment count category id " . $ilance->GPC['cid']);
		$ilance->email->mail = SITE_EMAIL;
		$ilance->email->slng = fetch_site_slng();
		$ilance->email->get('updateauction_moderation_admin');
		$ilance->email->set(array (
		    '{{project_title}}' => $ilance->GPC['project_title'],
		    '{{category}}' => $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['cid']),
		    '{{p_id}}' => intval($ilance->GPC['rfpid']),
		    '{{closing_date}}' => print_date($ilance->GPC['date_end'], $ilconfig['globalserverlocale_globaltimeformat']),
		    '{{details}}' => ucfirst($ilance->GPC['project_details']),
		    '{{privacy}}' => ucfirst($ilance->GPC['bid_details']),
		));
		$ilance->email->send();
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management&note=approval');
	}
	else
	{
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management&note=updated');
	}
	exit();
}
else
{
	if ($ilance->GPC['cmd'] == 'new-item')
	{
		$area_title = '{_sell_new_item}';
		$page_title = SITE_NAME . ' - {_sell_new_item}';
		$topnavlink = array (
			'main_selling'
		);
		// #### main category being posted in ##########
		$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		$owner_id = $_SESSION['ilancedata']['user']['userid'];
		if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $cid) == false)
		{
			print_notice('{_this_is_a_nonposting_category}', '{_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings}', $ilpage['rfp'] . '?cmd=listings', '{_categories}');
			exit();
		}
		// ### double check to see if this category supports proxy bidding
		// #### if so, check to see if bid increments are setup properly..
		// #### if not, show error message advising user to contact admin to setup bid increments in this category
		if ($ilance->categories->proxy_bid_ready($_SESSION['ilancedata']['user']['slng'], 'product', $cid) == false)
		{
			print_notice('{_no_bid_increments_found_in_this_category}', '{_we_are_sorry_for_the_trouble_but_it_appears_this_category_is_a_proxy}', $ilpage['rfp'] . '?cmd=listings', '{_categories}');
			exit();
		}
		// #### prevent the top cats in breadcrumb to contain any fields from this form
		$show['nourlbit'] = true;
		$ilance->categories->breadcrumb($cid, 'product', $_SESSION['ilancedata']['user']['slng']);
		$navcrumb[""] = '{_sell_new_item}';
		$project_id = $ilance->auction_rfp->construct_new_auctionid();
		$_SESSION['ilancedata']['tmp']['new_project_id'] = $project_id;
		// #### saving as draft? #######################
		$draft = (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft']) ? 'checked="checked"' : '';
		$saveasdraft = '<label for="savedraft"><input type="checkbox" id="savedraft" name="saveasdraft" value="1" ' . $draft . ' /> {_save_this_auction_as_a_draft}</label>';
		$wysiwyg_area = can_post_html($owner_id)
			? print_wysiwyg_editor('description', '', 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, (990 - ($ilconfig['table_cellpadding'] * 2)), '200', '', 'ckeditor', $ilconfig['ckeditor_listingdescriptiontoolbar'])
			: print_wysiwyg_editor('description', '', 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, (990 - ($ilconfig['table_cellpadding'] * 2)), '200', '', 'bbeditor');
		$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $project_id, 'input', 'product', 3);
		$bidfilters = $ilance->auction_post->print_bid_filters();
		$profilebidfilters = $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'product');
		// #### nonprofit support ######################
		$ilance->GPC['donationpercentage'] = 0;
		$ilance->GPC['charityid'] = isset($ilance->GPC['charityid']) ? intval($ilance->GPC['charityid']) : 0;
		$donationchecked = '';
		if ($ilconfig['enablenonprofits'] AND isset($ilance->GPC['donation']) AND $ilance->GPC['donation'])
		{
			$onload .= "toggle_show('donationbox'); ";
			$donationchecked = 'checked="checked"';
		}
		// #### scheduled realtime auction support #####
		if (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'])
		{
			$ilance->GPC['project_details'] = 'realtime';
		}
		// #### item location ##########################
		$city = $_SESSION['ilancedata']['user']['city'];
		$state = $_SESSION['ilancedata']['user']['state'];
		$zipcode = (mb_strtolower($_SESSION['ilancedata']['user']['postalzip']) == '{_unknown}') ? mb_strtolower($_SESSION['ilancedata']['user']['postalzip']) : mb_strtoupper($_SESSION['ilancedata']['user']['postalzip']);
		$country = $_SESSION['ilancedata']['user']['country'];
	}
	// #### EDIT AND RELIST PRODUCT AUCTION ################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'relist')
	{
		$area_title = '{_sell_new_item}';
		$page_title = SITE_NAME . ' - {_sell_new_item}';
		// #### main category being posted in ##########
		$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		$owner_id = $_SESSION['ilancedata']['user']['userid'];
		if (fetch_project_ownerid($ilance->GPC['id']) == $_SESSION['ilancedata']['user']['userid'])
		{
			$old_id = $ilance->GPC['id'];
		}
		else
		{
			print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
			exit();
		}
		if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $ilance->GPC['cid']) == false)
		{
			print_notice('{_this_is_a_nonposting_category}', '{_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings}', $ilpage['rfp'] . '?cmd=listings', '{_categories}');
			exit();
		}
		// #### prevent the top cats in breadcrumb to contain any fields from this form
		$show['nourlbit'] = true;
		$show['relist'] = true;
		$ilance->categories->breadcrumb($cid, 'product', $_SESSION['ilancedata']['user']['slng']);
		$navcrumb[""] = '{_sell_new_item}';
		$project_id = $ilance->auction_rfp->construct_new_auctionid_bulk();
		$_SESSION['ilancedata']['tmp']['new_project_id'] = $project_id;
		$ilance->auction->rewrite_photos($old_id, $project_id);
		$sql = $ilance->db->query("
			SELECT p.*, s.*, sd.*
			FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
			LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations sd ON p.project_id = sd.project_id
			LEFT JOIN " . DB_PREFIX . "projects_shipping_regions sr ON p.project_id = sr.project_id
			WHERE p.project_id = '" . intval($old_id) . "'
				AND p.project_state = 'product'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$ilance->GPC = array_merge($ilance->GPC, $ilance->db->fetch_array($sql, DB_ASSOC));
			// can we update auction?
			$show['noupdateauction'] = ($ilance->GPC['status'] == 'open' OR $ilance->GPC['status'] == 'draft') ? 0 : 1;
			$date_end = $ilance->GPC['date_end'];
		}
		else
		{
			print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
			exit();
		}
		// #### saving as draft? #######################
		$draft = (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft']) ? 'checked="checked"' : '';
		$saveasdraft = '<label for="savedraft"><input type="checkbox" id="savedraft" name="saveasdraft" value="1" ' . $draft . ' /> {_save_this_auction_as_a_draft}</label>';
		$bidfilters = $ilance->auction_post->print_bid_filters();
		$profilebidfilters = $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'product');
		$show['purchase_now'] = ($ilance->GPC['buynow_price'] > 0) ? 1 : 0;
		$draft = ($ilance->GPC['status'] == 'draft') ? 'checked="checked"' : '';
		$hiddenfields = print_hidden_fields(false, array ('invoiceid', 'escrow_id', 'bids', 'budgetgroup', 'transfer_to_userid', 'transfer_from_userid', 'cmd', 'project_id', 'project_type', 'rfpid', 'state', 'updateid', 'fvf', 'insertionfee', 'currentprice', 'project_state', 'transfertype', 'close_date', 'status', 'views', 'visible', 'user_id', 'date_added', 'id', 'isfvfpaid', 'isifpaid', 'ifinvoiceid', 'fvfinvoiceid', 'bidsdeclined', 'bidsretracted', 'bidsshortlisted', 'sellermarkedasshipped', 'sellermarkedasshippeddate', 'buyerfeedback', 'sellerfeedback', 'haswinner', 'hasbuynowwinner', 'winner_user_id', 'winnermarkedaspaid', 'winnermarkedaspaiddate', 'donermarkedaspaid', 'donermarkedaspaiddate'), false, 'old[', ']');
		$wysiwyg_area = (isset($ilance->GPC['ishtml']) AND $ilance->GPC['ishtml'] == '1')
			? print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, (990 - ($ilconfig['table_cellpadding'] * 2)), '200', '', 'ckeditor', $ilconfig['ckeditor_listingdescriptiontoolbar'])
			: print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, (990 - ($ilconfig['table_cellpadding'] * 2)), '200', '', 'bbeditor');
		$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $old_id, 'update', 'product', 3);
		$bidfilters = $ilance->auction_post->print_bid_filters();
		$profilebidfilters = $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'product');
		$date_end = $ilance->GPC['date_end'];
		$extendauction = $ilance->auction_post->print_extend_auction('extend');
		// #### rebuild selected auction enhancements ##########
		$show['disableselectedenhancements'] = false;
		if ($ilance->GPC['featured'])
		{
			$ilance->GPC['enhancements']['featured'] = 1;
		}
		if ($ilance->GPC['featured_searchresults'])
		{
			$ilance->GPC['enhancements']['featured_searchresults'] = 1;
		}
		if ($ilance->GPC['highlite'])
		{
			$ilance->GPC['enhancements']['highlite'] = 1;
		}
		if ($ilance->GPC['bold'])
		{
			$ilance->GPC['enhancements']['bold'] = 1;
		}
		if ($ilance->GPC['autorelist'])
		{
			$ilance->GPC['enhancements']['autorelist'] = 1;
		}
		// #### re populate the invitation user list ###########
		$invitesql = $ilance->db->query("
			SELECT invite_message, email, name
			FROM " . DB_PREFIX . "project_invitations
			WHERE project_id = '" . intval($old_id) . "'
				AND email != ''
				AND name != ''
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($invitesql) > 0)
		{
			while ($inviteres = $ilance->db->fetch_array($invitesql, DB_ASSOC))
			{
				$ilance->GPC['invitelist']['email'][] = $inviteres['email'];
				$ilance->GPC['invitelist']['name'][] = $inviteres['name'];
				$ilance->GPC['invitemessage'] = $inviteres['invite_message'];
			}
		}
		// #### determine if bids are placed ###################
		if ($ilance->GPC['bids'] > 0)
		{
			if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] AND isset($ilance->GPC['admincp']) AND $ilance->GPC['admincp'])
			{
				// this is so admin can update all fields of the listing
				$show['bidsplaced'] = false;
			}
			else
			{
				if (isset($show['relist']) AND $show['relist'] == true)
				{
					$show['bidsplaced'] = false;
				}
				else
				{
					$show['bidsplaced'] = true;
					$show['eventtypechange'] = $show['escrowchange'] = $show['titlechange'] = $show['returnpolicychange'] = false;
				}
			}
		}
		// #### nonprofit support ##############################
		$donationchecked = '';
		if ($ilconfig['enablenonprofits'] AND isset($ilance->GPC['donation']) AND $ilance->GPC['donation'])
		{
			$onload .= "toggle_show('donationbox'); ";
			$donationchecked = 'checked="checked"';
		}
		// #### item location ##################################
		$city = $ilance->GPC['city'];
		$state = $ilance->GPC['state'];
		$zipcode = $ilance->GPC['zipcode'];
		$country = $ilance->GPC['country'];
		// #### currency selector ##############################
		$onload .= ($ilconfig['globalserverlocale_currencyselector']) ? 'currency_switcher(); ' : '';
		$duration = strtotime($ilance->GPC['date_end']) - strtotime($ilance->GPC['date_starts']);
		if ($duration / 60 > 0 AND $duration / 60 <= 30)
		{
			$ilance->GPC['duration_unit'] = 'M';
			$ilance->GPC['duration'] = $duration / 60;
		}
		else if ($duration / 3600 > 0 AND $duration / 3600 <= 30)
		{
			$ilance->GPC['duration_unit'] = 'H';
			$ilance->GPC['duration'] = $duration / 3600;
		}
		else if ($duration / 86400 > 0 AND $duration / 86400 <= 30)
		{
			$ilance->GPC['duration_unit'] = 'D';
			$ilance->GPC['duration'] = $duration / 86400;
		}
	}
	// #### UPDATE PRODUCT AUCTION #########################
	else
	{
		$area_title = '{_update_item}';
		$page_title = SITE_NAME . ' - {_update_item}';
		$project_id = intval($ilance->GPC['id']);
		$owner_id = $_SESSION['ilancedata']['user']['userid'];
		$sql = $ilance->db->query("
			SELECT p.*, s.*, sd.*
			FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
			LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations sd ON p.project_id = sd.project_id
			LEFT JOIN " . DB_PREFIX . "projects_shipping_regions sr ON p.project_id = sr.project_id
			WHERE p.project_id = '" . intval($ilance->GPC['id']) . "'
				AND p.project_state = 'product'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$ilance->GPC = array_merge($ilance->GPC, $ilance->db->fetch_array($sql, DB_ASSOC));
			$show['noupdateauction'] = ($ilance->GPC['status'] == 'open' OR $ilance->GPC['status'] == 'draft') ? 0 : 1;
			$date_end = $ilance->GPC['date_end'];
			$ilance->GPC['attachmentlist'] = '';
			$sql_attach = $ilance->db->query("
				SELECT filename
				FROM " . DB_PREFIX . "attachment
				WHERE project_id = '" . $project_id . "'
					AND user_id = '" . intval($owner_id) . "'
					AND (attachtype = 'itemphoto' OR attachtype = 'slideshow') 
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_attach) > 0)
			{
				while ($res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC))
				{
					$ilance->GPC['attachmentlist'] .= $res_attach['filename'] . ',<br /> ';
				}
			}
		}
		else
		{
			print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
			exit();
		}
		// #### ADMIN UPDATING LISTING? ########################
		if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] AND isset($ilance->GPC['admincp']) AND $ilance->GPC['admincp'])
		{
			// auction owner from projects table not from session
			$owner_id = $ilance->GPC['user_id'];
			// inline auction ajax controls
			$headinclude .= "<script type=\"text/javascript\">
<!--
var searchid = 0;
var value = '';
var type = '';
var imgtag = '';
var favoriteicon = '';
var status = '';
function fetch_response()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
	{
		// format response
		response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
		phpstatus = xmldata.fetch_data(response);
		
		searchiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
		status = searchiconsrc.match(/\/unchecked.gif/gi);
		if (status == '/unchecked.gif')
		{
		       status = 'unchecked';
		}
		else
		{
		       status = 'checked';
		}                                
		if (status == 'unchecked')
		{
			if (phpstatus == 'on' || phpstatus == 'off')
			{
				favoriteiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
				imgtag = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '');
				
				favoriteicon2 = favoriteiconsrc.replace(/unchecked.gif/gi, 'working.gif');
				imgtag.src = favoriteicon2;
				
				favoriteicon = favoriteiconsrc.replace(/unchecked.gif/gi, 'checked.gif');
				var t = window.setTimeout('reset_image()', 700);
			}
			else
			{
				alert_js(phpstatus);
			}
		}
		else if (status == 'checked')
		{
			if (phpstatus == 'on' || phpstatus == 'off')
			{
				favoriteiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
				imgtag = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '');
				
				favoriteicon2 = favoriteiconsrc.replace(/checked.gif/gi, 'working.gif');
				imgtag.src = favoriteicon2;
	
				favoriteicon = favoriteiconsrc.replace(/checked.gif/gi, 'unchecked.gif');
				var t = window.setTimeout('reset_image()', 700);
			}
			else
			{
				alert_js(phpstatus); 
			}
		}
		xmldata.handler.abort();
	}
}
function update_enhancement(searchid, type)
{                        
	// set ajax handler
	xmldata = new AJAX_Handler(true);
	
	// url encode the vars
	searchid = urlencode(searchid);
	xmldata.searchid = searchid;
	
	type = urlencode(type);
	xmldata.type = type;
	
	searchiconsrc = fetch_js_object('inline_enhancement_' + searchid + '_' + type + '').src;
	status = searchiconsrc.match(/\/unchecked.gif/gi);
	if (status == '/unchecked.gif')
	{
	       value = 'on';
	}
	else
	{
	       value = 'off';
	}
	xmldata.onreadystatechange(fetch_response);
	
	// send data to php
	xmldata.send(AJAXURL, 'do=acpenhancements&value=' + value + '&id=' + searchid + '&type=' + type + '&s=' + ILSESSION + '&token=' + ILTOKEN);                        
}
//-->
</script>
";
			$show['purchase_now'] = ($ilance->GPC['buynow_price'] > 0) ? 1 : 0;
			// purchase now activity information (product auction view)
			$result_orders = $ilance->db->query("
				SELECT orderid, project_id, buyer_id, owner_id, invoiceid, attachid, qty, amount, escrowfee, escrowfeebuyer, fvf, fvfbuyer, isescrowfeepaid, isescrowfeebuyerpaid, isfvfpaid, isfvfbuyerpaid, escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid, ship_required, ship_location, orderdate, canceldate, arrivedate, paiddate, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost, buyerfeedback, sellerfeedback, status
				FROM " . DB_PREFIX . "buynow_orders
				WHERE owner_id = '" . intval($ilance->GPC['user_id']) . "'
					AND project_id = '" . intval($project_id) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result_orders) > 0)
			{
				$order_count = 0;
				while ($orderrows = $ilance->db->fetch_array($result_orders, DB_ASSOC))
				{
					$currencyid = fetch_auction('currencyid', $orderrows['project_id']);
					$orderrows['orderbuyer'] = fetch_user('username', $orderrows['buyer_id']);
					$orderrows['orderbuyer_id'] = $orderrows['buyer_id'];
					$orderrows['orderphone'] = fetch_user('phone', $orderrows['buyer_id']);
					$orderrows['orderemail'] = fetch_user('email', $orderrows['buyer_id']);
					$orderamount = fetch_auction('buynow_price', $orderrows['project_id']);
					$orderrows['orderamount'] = $ilance->currency->format($orderamount, $currencyid);
					$orderrows['escrowamount'] = '<strong>' . $ilance->currency->format($orderrows['amount'], $currencyid) . '</strong>';
					$orderrows['orderdate'] = print_date($orderrows['orderdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$orderrows['orderqty'] = $orderrows['qty'];
					$orderrows['orderinvoiceid'] = $orderrows['invoiceid'];
					$orderrows['orderid'] = $orderrows['orderid'];
					$orderrows['escrowfee'] = $ilance->currency->format($orderrows['escrowfee']);
					$orderrows['fvf'] = $ilance->currency->format($orderrows['fvf']);
					$leftfeedback = 0;
					if ($ilance->feedback->has_left_feedback($orderrows['orderbuyer_id'], $orderrows['owner_id'], $orderrows['project_id'], 'buyer'))
					{
						// seller already rated buyer!
						$leftfeedback = 1;
						if ($ilance->feedback->is_feedback_complete($orderrows['project_id']))
						{
							$orderrows['feedback2'] = '<div align="center"><span title="{_feedback_complete}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checked.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span></div>';
						}
						else
						{
							$orderrows['feedback2'] = '<div align="center"><span title="{_feedback_submitted__thank_you}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span></div>';
						}
					}
					else
					{
						$orderrows['feedback2'] = '<div align="center"><span title="{_submit_feedback_for} ' . fetch_user('username', $orderrows['buyer_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2&amp;returnurl={pageurl_urlencoded}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="{_submit_feedback_for} ' . fetch_user('username', $orderrows['buyer_id']) . '" /></a></span></div>';
					}
					// escrow order status
					if ($orderrows['status'] == 'paid')
					{
						// started - funds forwarded by bidder into escrow for item
						$orderrows['bgcolclass'] = '#FEFFE5';
						$orderrows['orderstatus'] = '{_funds_secured}';
						$crypted = array (
							'cmd' => 'management',
							'sub' => 'buynow-escrow',
							'subcmd' => '_confirm-delivery',
							'id' => $orderrows['orderid']
						);
						$orderrows['orderactions'] = '<a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}" onclick="return confirm_js(phrase[\'_confirm_delivery_the_items_have_already_been_shipped_to_buyers_location_additionally_js\'])" style="text-decoration:underline">{_mark_as_shipped}</a><div style="padding-top:4px; padding-bottom:4px"><strong>OR</strong></div>';
						$crypted = array (
						    'cmd' => 'management',
						    'sub' => 'buynow-escrow',
						    'subcmd' => '_cancel-delivery',
						    'id' => $orderrows['orderid']
						);
						$orderrows['orderactions'] .= '<a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}" target="_self" onclick="return confirm_js(phrase[\'_cancel_delivery_return_funds_in_escrow_back_to_this_buyer_continue\'])">{_cancel_order}</a>';
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id']);
						$orderrows['share2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
						$show['escrowcolumn']['project_id'] = 1;
					}
					else if ($orderrows['status'] == 'pending_delivery')
					{
						// started - funds forwarded by bidder into escrow for item
						$orderrows['bgcolcolor'] = '#FFFFED';
						$orderrows['orderactions'] = '<strong>{_pending_release}</strong> <a href="javascript:void(0)" onmouseover="Tip(phrase[\'_waiting_for_buyer_to_release_funds_into_my_online_account\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id']);
						$orderrows['share2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
						$show['escrowcolumn']['project_id'] = 1;
					}
					else if ($orderrows['status'] == 'delivered')
					{
						// started - funds forwarded by bidder into escrow for item
						$orderrows['bgcolcolor'] = '#EAFFE5';
						if ($leftfeedback)
						{
							$orderrows['orderactions'] = '<strong>{_completed}</strong>';
						}
						else
						{
							$orderrows['bgcolcolor'] = '#FFFFED';
							$orderrows['orderactions'] = '{_leave_feedback}';
						}
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id']);
						$orderrows['share2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
						$show['escrowcolumn']['project_id'] = 1;
					}
					else if ($orderrows['status'] == 'cancelled')
					{
						// started - funds forwarded by bidder into escrow for item
						$orderrows['orderactions'] = '{_cancelled}';
						$orderrows['bgcolcolor'] = '#FCF2F2';
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id']);
						$orderrows['share2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = false);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = false);
						$show['escrowcolumn']['project_id'] = 1;
					}
					else if ($orderrows['status'] == 'offline')
					{
						$orderrows['escrowamount'] = '-';
						$orderrows['fees'] = '-';
						$orderrows['bgcolcolor'] = '#FEFFE5';
						$crypted = array (
							'cmd' => 'management',
							'sub' => 'buynow-escrow',
							'subcmd' => '_confirm-offline-delivery',
							'id' => $orderrows['orderid']
						);
						$orderrows['orderactions'] = '<a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '" onclick="return confirm_js(phrase[\'_confirm_delivery_the_items_have_already_been_shipped_to_buyers_location_additionally_js\'])" style="text-decoration:underline"><strong>{_mark_as_shipped}</strong></a> <a href="javascript:void(0)" onmouseover="Tip(phrase[\'_confirm_delivery_means_that_it_is_up_to_you_as_the_merchant_to_confirm_the_delivery\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon(intval($ilance->GPC['user_id']), $orderrows['owner_id'], $orderrows['project_id']);
						$orderrows['share2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
					}
					else if ($orderrows['status'] == 'offline_delivered')
					{
						$orderrows['escrowamount'] = '-';
						$orderrows['fees'] = '-';
						$orderrows['bgcolcolor'] = '#FFFFED';
			
						if ($leftfeedback == 1)
						{
							$orderrows['bgcolcolor'] = '#EAFFE5';
							$orderrows['orderactions'] = '<strong>{_completed}</strong>';
						}
						else
						{
							$orderrows['orderactions'] = '{_leave_feedback}';
						}
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon(intval($ilance->GPC['user_id']), $orderrows['owner_id'], $orderrows['project_id']);
						$orderrows['share2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon(intval($ilance->GPC['user_id']), $orderrows['orderbuyer_id'], $orderrows['project_id'], $active = true);
					}
					if ($orderrows['ship_required'])
					{
						$orderrows['shippinginfo'] = $ilance->currency->format($orderrows['buyershipcost'], $currencyid);
						$orderrows['orderlocation'] = handle_input_keywords($orderrows['ship_location']);
					}
					else
					{
						$digitalfile = '';
						$orderrows['buyershipcost'] = 0;
						$orderrows['shippinginfo'] = $ilance->currency->format($orderrows['buyershipcost'], $currencyid);
						$dquery = $ilance->db->query("
							SELECT filename, counter, filesize, attachid
							FROM " . DB_PREFIX . "attachment
							WHERE project_id = '" . intval($orderrows['project_id']) . "'
								AND attachtype = 'digital'
								AND user_id = '" . intval($ilance->GPC['user_id']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($dquery) > 0)
						{
							$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
							$digitalfile = '<strong>' . stripslashes(handle_input_keywords($dfile['filename'])) . '</strong> (' . print_filesize($dfile['filesize']) . ')';
						}
						$orderrows['orderlocation'] = '{_digital_delivery}' . ': ' . $digitalfile;
					}
		    
					($apihook = $ilance->api('selling_management_buy_now_end')) ? eval($apihook) : false;
		    
					$orderrows['total'] = $ilance->currency->format(($orderamount * $orderrows['orderqty']) + $orderrows['buyershipcost'], $currencyid);
					$orderrows['class'] = ($order_count % 2) ? 'alt2' : 'alt1';
					$GLOBALS['purchase_now_activity'][] = $orderrows;
					$order_count++;
				}
			}
			else
			{
				$GLOBALS['no_purchase_now_activity'][] = 1;
			}
			$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($ilance->GPC['cid'], $cattype = 'product', $type = 'level', $fieldname = 'cid', 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = 0, $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = false, $showbestmatching = false, $ilance->categories->cats);
			if ($ilance->GPC['visible'])
			{
				$auctionvisible = ($ilance->GPC['status'] == 'draft') ? '<label for="visible1"><input type="radio" name="visible" value="1" disabled="disabled" id="visible1" /> {_yes}</label> <label for="visible0"><input type="radio" name="visible" value="0" checked="checked" id="visible0" /> {_no}</label>' : '<label for="visible1"><input type="radio" name="visible" value="1" checked="checked" id="visible1" /> {_yes}</label> <label for="visible0"><input type="radio" name="visible" value="0" id="visible0" /> {_no}</label>';
			}
			else
			{
				$auctionvisible = '<label for="visible1"><input type="radio" name="visible" value="1" id="visible1" /> {_yes}</label> <label for="visible0"><input type="radio" name="visible" value="0" checked="checked" id="visible0" /> {_no}</label>';
			}
			$project_state_pulldown = $ilance->admincp->auction_state_pulldown($project_id);
			$project_details_pulldown = $ilance->admincp->auction_details_pulldown($ilance->GPC['project_details'], 0, 'product');
			$status_pulldown = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], false, 'product');
			$enhancement_list = $ilance->admincp->fetch_auction_enhancements_list($project_id);
			$date_added = $ilance->GPC['date_added'];
			$date_starts = $ilance->GPC['date_starts'];
		}
		// #### main category ##################################
		$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		$old_id = (isset($ilance->GPC['id']) AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'relist') ? $ilance->GPC['id'] : '';
		$show['nourlbit'] = true;
		$ilance->categories->breadcrumb($cid, 'product', $_SESSION['ilancedata']['user']['slng']);
		$navcrumb[""] = '{_update_item}';
		$draft = ($ilance->GPC['status'] == 'draft') ? 'checked="checked"' : '';
		$hiddenfields = print_hidden_fields(false, array ('invoiceid', 'escrow_id', 'bids', 'budgetgroup', 'transfer_to_userid', 'transfer_from_userid', 'cmd', 'project_id', 'project_type', 'rfpid', 'state', 'updateid', 'fvf', 'insertionfee', 'currentprice', 'project_state', 'transfertype', 'close_date', 'status', 'views', 'visible', 'user_id', 'date_added', 'id', 'isfvfpaid', 'isifpaid', 'ifinvoiceid', 'fvfinvoiceid', 'bidsdeclined', 'bidsretracted', 'bidsshortlisted', 'sellermarkedasshipped', 'sellermarkedasshippeddate', 'buyerfeedback', 'sellerfeedback', 'haswinner', 'hasbuynowwinner', 'winner_user_id', 'winnermarkedaspaid', 'winnermarkedaspaiddate', 'donermarkedaspaid', 'donermarkedaspaiddate'), false, 'old[', ']');
		$wysiwyg_area = (isset($ilance->GPC['ishtml']) AND $ilance->GPC['ishtml'] == '1')
			? print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, (990 - ($ilconfig['table_cellpadding'] * 2)), '200', '', 'ckeditor', $ilconfig['ckeditor_listingdescriptiontoolbar'])
			: print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, (990 - ($ilconfig['table_cellpadding'] * 2)), '200', '', 'bbeditor');
		$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $project_id, 'update', 'product', 3);
		$bidfilters = $ilance->auction_post->print_bid_filters();
		$profilebidfilters = $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'product');
		$date_end = $ilance->GPC['date_end'];
		$show['gtc'] = (isset($ilance->GPC['gtc']) AND $ilance->GPC['gtc']) ? true : false;
		$extendauction = $ilance->auction_post->print_extend_auction('extend');
		$show['disableselectedenhancements'] = true;
		if ($ilance->GPC['featured'])
		{
			$ilance->GPC['enhancements']['featured'] = 1;
		}
		if ($ilance->GPC['featured_searchresults'])
		{
			$ilance->GPC['enhancements']['featured_searchresults'] = 1;
		}
		if ($ilance->GPC['highlite'])
		{
			$ilance->GPC['enhancements']['highlite'] = 1;
		}
		if ($ilance->GPC['bold'])
		{
			$ilance->GPC['enhancements']['bold'] = 1;
		}
		if ($ilance->GPC['autorelist'])
		{
			$ilance->GPC['enhancements']['autorelist'] = 1;
		}
		// #### re populate the invitation user list ###########
		$invitesql = $ilance->db->query("
			SELECT invite_message, email, name
			FROM " . DB_PREFIX . "project_invitations
			WHERE project_id = '" . intval($project_id) . "'
				AND email != ''
				AND name != ''
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($invitesql) > 0)
		{
			while ($inviteres = $ilance->db->fetch_array($invitesql, DB_ASSOC))
			{
				$ilance->GPC['invitelist']['email'][] = $inviteres['email'];
				$ilance->GPC['invitelist']['name'][] = $inviteres['name'];
				$ilance->GPC['invitemessage'] = $inviteres['invite_message'];
			}
		}
		// #### determine if bids are placed ###################
		if ($ilance->GPC['bids'] > 0)
		{
			if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] AND isset($ilance->GPC['admincp']) AND $ilance->GPC['admincp'])
			{
				// this is so admin can update all fields of the listing
				$show['bidsplaced'] = false;
			}
			else
			{
				$show['bidsplaced'] = true;
				$show['eventtypechange'] = $show['escrowchange'] = $show['titlechange'] = $show['returnpolicychange'] = false;
			}
		}
		// #### nonprofit support ##############################
		$donationchecked = '';
		if ($ilconfig['enablenonprofits'] AND isset($ilance->GPC['donation']) AND $ilance->GPC['donation'])
		{
			$onload .= "toggle_show('donationbox'); ";
			$donationchecked = 'checked="checked"';
		}
		// #### item location ##################################
		$city = $ilance->GPC['city'];
		$state = $ilance->GPC['state'];
		$zipcode = $ilance->GPC['zipcode'];
		$country = $ilance->GPC['country'];
		// #### currency selector ##############################
		$onload .= ($ilconfig['globalserverlocale_currencyselector']) ? 'currency_switcher(); ' : '';
	}
}
// auction listing defaults
$status = 'open';
$project_type = 'forward';
// can user attach media?
$attachment_style = ($ilance->permissions->check_access($owner_id, 'attachments') == 'no') ? 'disabled="disabled"' : '';
// digital attachment upload button
$hiddeninput = array (
	'attachtype' => 'digital',
	'project_id' => $project_id,
	'user_id' => $owner_id,
	'category_id' => $cid,
	'filehash' => md5(time()),
	'max_filesize' => $ilance->permissions->check_access($owner_id, 'uploadlimit'),
	'attachmentlist' => 'digital_attachmentlist'
);
$uploaddigitalbutton = '<input name="attachment" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($hiddeninput) . '") type="button" value="{_upload}" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
unset($hiddeninput);
$digital_attachmentlist = fetch_inline_attachment_filelist($owner_id, $project_id, 'digital', false);
// #### some javascript above the template (not between <head>..)
$js_start = $ilance->auction_post->print_js('product');
// #### build an if condition to either show advanced profile filters or hide them if none available
$filter_quantity = $ilance->auction_post->get_filters_quantity($cid);
// #### auction title ##########################################
if ($ilance->GPC['cmd'] == 'new-item')
{
	$title = $ilance->auction_post->print_title_input('project_title');
	// #### seller currency selector ###############################
	$currencypulldown = ($ilconfig['globalserverlocale_currencyselector']) ? $ilance->currency->print_currency_pulldown($_SESSION['ilancedata']['user']['currencyid'], true) : '';
	$onload .= ($ilconfig['globalserverlocale_currencyselector']) ? 'currency_switcher(); ' : '';
}
else
{
	if ($ilconfig['globalfilters_changeauctiontitle'] == '1' AND $show['bidsplaced'] == false)
	{
		$title = $ilance->auction_post->print_title_input('project_title');
	}
	else
	{
		$title = $ilance->auction_post->print_title_input('project_title', true);
	}
	// #### seller currency selector ###############################
	$ilance->GPC['currencyid'] = $ilance->GPC['currencyid'] != '0' ? intval($ilance->GPC['currencyid']) : $ilconfig['globalserverlocale_defaultcurrency'];
	$currencypulldown = ($ilconfig['globalserverlocale_currencyselector']) ? $ilance->currency->print_currency_pulldown(intval($ilance->GPC['currencyid']), true, $disabled = $show['bidsplaced']) : '';
}
// #### currency pulldown selector template conditional ################
$show['currencypulldown'] = (!empty($currencypulldown)) ? true : false;
$title = $ilance->auction_post->print_title_input('project_title', $show['bidsplaced']);
// #### video description cost #########################################
$videodescriptioncost = ($ilconfig['productupsell_videodescriptioncost'] > 0) ? $ilance->currency->format($ilconfig['productupsell_videodescriptioncost']) : '{_free}';
// #### video description ##############################################
$description_videourl = $ilance->auction_post->print_video_description_input('description_videourl', $show['bidsplaced']);
// #### keywords input #################################################
$keywordinput = $ilance->auction_post->print_keywords_input('keywords', $show['bidsplaced']);
// #### item identification input ######################################
$sku = $ilance->auction_post->print_itemid_input('sku', $show['bidsplaced']);
$partnumber = $ilance->auction_post->print_itemid_input('partnumber', $show['bidsplaced']);
$modelnumber = $ilance->auction_post->print_itemid_input('modelnumber', $show['bidsplaced']);
$upc = $ilance->auction_post->print_itemid_input('upc', $show['bidsplaced']);
$ean = $ilance->auction_post->print_itemid_input('ean', $show['bidsplaced']);
// #### auction event access ###########################################
$auctioneventtype = $ilance->auction_post->print_event_type_filter('product', 'project_details', $show['bidsplaced']);
// #### duration code logic ############################################
$duration = isset($ilance->GPC['duration']) ? intval($ilance->GPC['duration']) : '';
$duration = $ilance->auction_post->duration($duration, 'duration', $show['bidsplaced'], 'D', true, $cid);
// #### realtime scheduled event date/time #####################
$durationbits = isset($ilance->GPC['duration_unit']) ? intval($ilance->GPC['duration_unit']) : 'D';
$durationbits = $ilance->auction_post->print_duration_logic($durationbits, 'duration_unit', $show['bidsplaced'], 'duration', true, $cid);
// #### invitation options and controls ################################
$inviteoptions = $ilance->auction_post->print_invitation_controls('product');
// #### bidding privacy ################################################
$biddingprivacy = $ilance->auction_post->print_bid_privacy('bid_details', $show['bidsplaced']);
// #### selling format #################################################
$sellingformat = $ilance->auction_post->print_selling_format_logic($show['bidsplaced']);
// #### escrow filter (if enabled, javascript will hide the payment methods input box on preview also)
$escrowfilter = $ilance->auction_post->print_escrow_filter($cid, 'product', 'productmerchant', false);
// #### shipping #######################################################
$shipping = $ilance->auction_post->print_shipping_logic($show['bidsplaced']);
// #### ship handling time and fee if applicable #######################
$shipping_handling = $ilance->auction_post->print_ship_handling_logic($show['bidsplaced']);
// #### item return policy #############################################
$returnpolicy = $ilance->auction_post->print_return_policy($show['bidsplaced']);
// #### public message boards? #########################################
$publicboard = $ilance->auction_post->print_public_board('filter_publicboard');
// #### handle listing enhancement upsell fees #########################
$enhancements = $ilance->auction_post->print_listing_enhancements('product');
$show['enhancements_block'] = ($enhancements == "") ? false : true;
$onload .= ' calculate_insertionfees();';
$headinclude .= '<script type="text/javascript">
<!--
if (!window.XMLHttpRequest)
{
        var reqObj = 
        [
                function() {return new ActiveXObject("Msxml2.XMLHTTP");},
                function() {return new ActiveXObject("Microsoft.XMLHTTP");},
                function() {return window.createRequest();}
        ];
        for(a = 0, z = reqObj.length; a < z; a++)
        {
                try
                {
                        window.XMLHttpRequest = reqObj[a];
                        break;
                }
                catch(e)
                {
                        window.XMLHttpRequest = null;
                }
        }
}
var req = new XMLHttpRequest();
function calculate_insertionfees()
{
	if (fetch_js_object(\'cid\').value != \'\')
	{
		cid = fetch_js_object(\'cid\').value;
	}
	else
	{
		cid = ' . $cid . ';
	}
	startprice = fetch_js_object(\'startprice\').value;
	reserve_price = fetch_js_object(\'reserve_price\').value;
	buynow_price = fetch_js_object(\'buynow_price\').value;
	buynow_price_fixed = fetch_js_object(\'buynow_price_fixed\').value;
	if (parseFloat(buynow_price) < parseFloat(buynow_price_fixed))
	{
		buynow_price = buynow_price_fixed;
	}
	if (fetch_js_object(\'currencyoptions\'))
	{
		currencyid = fetch_js_object(\'currencyoptions\').options[fetch_js_object(\'currencyoptions\').selectedIndex].value;
	}
	else
	{
		currencyid = ' . $ilconfig['globalserverlocale_defaultcurrency'] . ';
	}
	req.open(\'GET\', \'' . AJAXURL . '?do=calculateinsertionfees&cid=\' + cid + \'&startprice=\' + startprice + \'&reserve_price=\' + reserve_price + \'&buynow_price=\' + buynow_price + \'&currencyid=\' + currencyid);
	req.send(null); 
}
req.onreadystatechange = function()
{
	if (req.readyState == 4 && req.status == 200)
	{
		var myString;
		myString = req.responseText;
		myString = myString.split("|");
		obj = fetch_js_object(\'insertionfees\');
		obj.innerHTML = myString[0];
		obj2 = fetch_js_object(\'finalvaluefees\');
		obj2.innerHTML = myString[1];
	}
}  
//-->
</script>
';
$project_details = isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : 'public';
// #### charities for donations ########################################
$charitiespulldown = $ilance->auction_post->print_charities_pulldown($ilance->GPC['charityid']);
$donationpercentage = $ilance->auction_post->print_donation_percentage($ilance->GPC['donationpercentage']);
// #### construct countries / states pulldown ##########################
$jscountry = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : $ilconfig['registrationdisplay_defaultcountry'];
$jsstate = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : $ilconfig['registrationdisplay_defaultstate'];
$jscity = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : $ilconfig['registrationdisplay_defaultcity'];
$countryid = fetch_country_id($jscountry, $_SESSION['ilancedata']['user']['slng']);
$country_js_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $jscountry, 'filtered_country', false, 'filtered_state', false, false, false, 'stateid');
$state_js_pulldown = '<div id="stateid" style="height:20px">' . $ilance->common_location->construct_state_pulldown($countryid, $jsstate, 'filtered_state') . '</div>';
unset($countryid);
// #### item location modal ############################################
$ilance->GPC['country'] = isset($ilance->GPC['country']) ? $ilance->GPC['country'] : $_SESSION['ilancedata']['user']['country'];
$ilance->GPC['state'] = isset($ilance->GPC['state']) ? $ilance->GPC['state'] : $_SESSION['ilancedata']['user']['state'];
$ilance->GPC['city'] = isset($ilance->GPC['city']) ? $ilance->GPC['city'] : $_SESSION['ilancedata']['user']['city'];
$countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
$full_country_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $ilance->GPC['country'], 'modal_country', false, 'modal_state', false, false, false, 'stateid2', false, 'salestaxstate', 'modal_country', 'stateid3', '', false, false, '', 0, 'modal_city', 'cityid2');
$full_states_pulldown = '<div id="stateid2">' . $ilance->common_location->construct_state_pulldown($countryid, $ilance->GPC['state'], 'modal_state', false, false, 0, '', 0, 'modal_city', 'cityid2') . '</div>';
$full_cities_pulldown = '<div id="cityid2">' . $ilance->common_location->construct_city_pulldown($ilance->GPC['state'], 'modal_city', $ilance->GPC['city'], false, false, '') . '</div>';
// #### sales tax if applicable ########################################
$salestax = $ilance->auction_post->print_sales_tax_logic($countryid, $ilance->GPC['state'], 'salestaxstate', $show['bidsplaced']);
// #### shipping calculator modal ######################################
$shipfrom_country_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $ilance->GPC['country'], 'modal_country_from', false, 'modal_state_from', false, false, false, 'stateid4', true, '', '', '', '', false, false, '', 0, 'modal_city_from', 'cityid4');
$shipfrom_states_pulldown = '<div id="stateid4">' . $ilance->common_location->construct_state_pulldown($countryid, $ilance->GPC['state'], 'modal_state_from', false, false, 0, '', 0, 'modal_city_from', 'cityid4') . '</div>';
$shipfrom_city_pulldown = '<div id="cityid4">' . $ilance->common_location->construct_city_pulldown($ilance->GPC['state'], 'modal_city_from', $ilance->GPC['city'], false, false, 'width:140px') . '</div>';
$shipto_country_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $ilance->GPC['country'], 'modal_country_to', false, 'modal_state_to', false, false, false, 'stateid5', true, '', '', '', '', false, false, '', 0, 'modal_city_to', 'cityid5');
$shipto_states_pulldown = '<div id="stateid5">' . $ilance->common_location->construct_state_pulldown($countryid, $ilance->GPC['state'], 'modal_state_to', false, false, 0, '', 0, 'modal_city_to', 'cityid5') . '</div>';
$shipto_city_pulldown = '<div id="cityid5">' . $ilance->common_location->construct_city_pulldown($ilance->GPC['state'], 'modal_city_to', $ilance->GPC['city'], false, false, 'width:140px') . '</div>';
// #### fee totals #####################################################
$feetotal = '0.00';
$ilance->template->fetch('main', 'listing_forward_auction_create.html');
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', 'purchase_now_activity');
$ilance->template->parse_if_blocks('main');
$pprint_array = array ('full_cities_pulldown','shipfrom_states_pulldown', 'shipfrom_city_pulldown', 'shipto_states_pulldown', 'shipto_city_pulldown', 'old_id', 'salestax', 'sku', 'upc', 'ean', 'partnumber', 'modelnumber', 'currencypulldown', 'old_id', 'shipping_handling', 'shipfrom_country_pulldown', 'shipto_country_pulldown', 'full_states_pulldown', 'full_country_pulldown', 'city', 'state', 'zipcode', 'country', 'uploaddigitalbutton', 'digital_attachmentlist', 'donationpercentage', 'donationchecked', 'videodescriptioncost', 'feetotal', 'slideshowcost', 'charitiespulldown', 'description_videourl', 'returnpolicy', 'date_added', 'date_starts', 'enhancement_list', 'status_pulldown', 'project_details_pulldown', 'project_state_pulldown', 'auctionvisible', 'category_pulldown', 'tab', 'hiddenfields', 'extendauction', 'date_end', 'slideshow_attachmentlist', 'itemphoto_attachmentlist', 'uploadproductbutton', 'inviteoptions', 'keywordinput', 'title', 'finalvaluefees', 'profilebidfilters', 'js_start', 'js_end', 'wysiwyg_area', 'insertionfees', 'additionalcategory', 'keywords', 'listingfees', 'invoicebit', 'slideshowinfo', 'productimageinfo', 'shipping', 'sellingformat', 'digitalfile', 'enhancements', 'saveasdraft', 'maincategory', 'pid', 'paymentmethods', 'attachmentlist', 'bidfilters', 'publicboard', 'biddingprivacy', 'durationbits', 'auctioneventtype', 'escrowfilter', 'budgetfilter', 'bidtypefilter', 'attachmentlist', 'additional_info', 'description', 'preview_pane', 'cid', 'js', 'state_js_pulldown', 'country_js_pulldown', 'bidamounttype_pulldown', 'moderationalert', 'project_questions', 'uploadbutton', 'project_title', 'budget_pulldown', 'duration', 'year', 'month', 'day', 'hour', 'min', 'sec', 'invitation', 'invitationid', 'country_pulldown', 'category', 'subcategory', 'filehash', 'max_filesize', 'attachment_style', 'user_id', 'state', 'catid', 'subcatid', 'currency', 'project_id');

($apihook = $ilance->api('new_product_auction_end')) ? eval($apihook) : false;

$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>