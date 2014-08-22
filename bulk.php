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
		'wysiwyg',
		'ckeditor'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### setup script location ##################################################
define('LOCATION', 'main');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
//$navcrumb = array("$ilpage[bulk]" => $ilcrumbs["$ilpage[bulk]"]);

// #### define top header nav ##########################################
$topnavlink = array(
	'main_bulk'
);

$area_title = '{_sell_products_and_services}';
$page_title = '{_bulk} | ' . SITE_NAME;
$navcrumb[""] = '{_sell}';
if (!isset($_SESSION['ilancedata']['user']['userid']) OR empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0 OR $ilconfig['globalfilters_bulkupload'] == '0')
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['bulk'] . print_hidden_fields(true, array(), true)));
	exit();
}
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] != 'sell' OR !isset($ilance->GPC['cmd']))
{
	refresh(HTTPS_SERVER . $ilpage['main']);
	exit();
}
$area_title = '{_sell_products_and_services}<div class="smaller">{_bulk}</div>';
$page_title = '{_sell} {_bulk} | ' . SITE_NAME;
if (isset($ilance->GPC['specifics']) AND $ilance->GPC['specifics'])
{
	$area_title = '{_sell_products_and_services}<div class="smaller">{_bulk} Specifics API</div>';
	$cid = !empty($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
}

$columns = array('project_title','description','startprice','buynow_price','reserve_price','buynow_qty','buynow_qty_lot','project_details','filtered_auctiontype','cid','sample');
$columnphrases = array(
	'project_title' => '{_title}',
	'description' => '{_description}',
	'startprice' => '{_starting_price}',
	'buynow_price' => '{_buy_now_price}',
	'reserve_price' => '{_reserve_price}',
	'buynow_qty' => '{_qty}',
	'buynow_qty_lot' => '{_lot}',
	'project_details' => '{_listing_type}',
	'filtered_auctiontype' => '{_bidding_type}',
	'cid' => '{_category_id}',
	'sample' => '{_sample}',
	'city' => '{_city}',
	'state' => '{_state}',
	'zipcode' => '{_zipcode}',
	'country' => '{_country}'
);
if ($ilconfig['globalserverlocale_currencyselector'])
{
	$tempcolumn = array('currency');
	$tempcolumnphrase = array('currency' => '{_currency}');
	$columns = array_merge($columns, $tempcolumn);
	$columnphrases = array_merge($columnphrases, $tempcolumnphrase);
	unset($tempcolumn, $tempcolumnphrase);
}
$tempcolumn = array(
	'city',
	'state',
	'zipcode',
	'country',
	'attributes',
	'sku',
	'upc',
	'partnumber',
	'modelnumber',
	'ean'
);

// developers can add custom column headers here
($apihook = $ilance->api('main_sell_bulk_column_headers_end')) ? eval($apihook) : false;

$tempcolumnphrase = array(
	'city' => '{_city}',
	'state' => '{_state}',
	'zipcode' => '{_zipcode}',
	'country' => '{_country}',
	'attributes' => '{_attributes}',
	'sku' => '{_sku}',
	'upc' => '{_upc}',
	'partnumber' => '{_part_number}',
	'modelnumber' => '{_model_number}',
	'ean' => '{_ean}'
);

$columnstyle = array(
	'project_title' => 'width:100px',
	'description' => 'width:150px',
	'startprice' => 'width:25px',
	'buynow_price' => 'width:25px',
	'reserve_price' => 'width:25px',
	'buynow_qty' => 'width:10px',
	'buynow_qty_lot' => 'width:10px',
	'project_details' => 'width:25px',
	'filtered_auctiontype' => 'width:25px',
	'cid' => 'width:20px',
	'attributes' => 'width:200px',
	'partnumber' => 'width:25px',
	'modelnumber' => 'width:25px',
);

// developers can add custom colum header phrases here
($apihook = $ilance->api('main_sell_bulk_column_phrases_end')) ? eval($apihook) : false;

$columns = array_merge($columns, $tempcolumn);
$columnphrases = array_merge($columnphrases, $tempcolumnphrase);
unset($tempcolumn, $tempcolumnphrase);
$coloumncount = count($columns);
$description_limit = $ilconfig['globalfilters_maxcharactersdescriptionbulk'];

// #### ASSIGN #################################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'assign')
{
	$show['widescreen'] = true;
	$navcrumb = array();
	$navcrumb[HTTP_SERVER . "$ilpage[bulk]?cmd=sell"] = '{_bulk}';
	$navcrumb[HTTP_SERVER . "$ilpage[bulk]?cmd=sell"] = '{_sell}';
	$navcrumb[""] = '{_preview}';
	$area_title = '{_sell_products_and_services}';
	$page_title = '{_sell} {_bulk} | ' . SITE_NAME;
	if (empty($_FILES['csv_file']['name']))
	{
		print_notice('{_invalid_bulk_import_file}', '{_the_bulk_file_you_uploaded_was_not_correct_please_try_again}', 'javascript:history.go(-1)', '{_retry}');
		exit();
	}
	else
	{
		$extension = mb_strtolower(mb_strrchr($_FILES['csv_file']['name'], '.'));
		if ($extension != '.csv')
		{
			print_notice('{_invalid_file_extension}', '{_the_bulk_file_you_uploaded_did_not_have_the_correct_file_extension}', 'javascript:history.go(-1)', '{_retry}');
			exit();
		}
	}
	while (list($key, $value) = each($_FILES))
	{
		$GLOBALS[$key] = $value;
		foreach ($_FILES AS $key => $value)
		{
			$GLOBALS[$key] = $_FILES[$key]['tmp_name'];
			foreach ($value AS $ext => $value2)
			{
				$key2 = $key . '_' . $ext;
				$GLOBALS[$key2] = $value2;
			}
		}
	}
	$tmp_name = $_FILES['csv_file']['tmp_name'];
	$file_name = DIR_TMP . $_FILES['csv_file']['name'];
	if (file_exists($file_name))
	{
		@unlink($file_name);
	}
	move_uploaded_file($tmp_name, $file_name);
	$data = file_get_contents($file_name);
	$data = str_replace(array("\r\n", "\r", "\n"), LINEBREAK, $data);
	file_put_contents($file_name, $data);
	$datetime = DATETIME24H;
	$sq2 = $ilance->db->query("
		INSERT INTO " . DB_PREFIX . "bulk_sessions
		(user_id, dateupload, itemsuploaded)
		VALUES (
		'" . $_SESSION['ilancedata']['user']['userid'] . "',
		'" . $ilance->db->escape_string($datetime) . "',
		'0')
	", 0, null, __FILE__, __LINE__);
	$sql3 = $ilance->db->query("
		SELECT id
		FROM " . DB_PREFIX . "bulk_sessions
		WHERE dateupload = '" . $ilance->db->escape_string($datetime) . "'
	", 0, null, __FILE__, __LINE__);
	$res = $ilance->db->fetch_array($sql3, DB_ASSOC);
	$bulk_id = $res['id'];
	// handle importing ####################################################
	$containsheader = isset($ilance->GPC['containsheader']) ? true : false;
	$ilance->csv->csv_to_db($file_name, $_SESSION['ilancedata']['user']['userid'], $bulk_id, $containsheader);
	// remove uploaded csv file...
	if (file_exists($file_name))
	{
		@unlink($file_name);
	}
	$sql = $ilance->db->query("
		SELECT id
		FROM " . DB_PREFIX . "bulk_tmp
		WHERE correct = '0'
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND bulk_id = '" . $bulk_id . "'
	", 0, null, __FILE__, __LINE__);
	$items = $ilance->db->num_rows($sql);
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "bulk_sessions
		SET items = '" . intval($items) . "'
		WHERE id = '" . intval($bulk_id) . "'
	", 0, null, __FILE__, __LINE__);
	$col = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
	$col .= '<tr class="alt2">';
	for ($i = 1; $i <= $coloumncount; $i++)
	{
		$col .= '<td><div><select name="col[' . $i . ']" style="font-family: verdana" class="smaller"><option value="">-</option>';
		$x = 1;
		foreach ($columns AS $key)
		{
			if (isset($columnphrases["$key"]) AND !empty($columnphrases["$key"]))
			{
				$col .= ($i == $x) ? '<option value="' . $key . '" selected="selected">' . $columnphrases["$key"] . '</option>' : '<option value="' . $key . '">' . $columnphrases["$key"] . '</option>';
				$x++;
			}
		}
		$col .= '</select></div></td>';
	}
	$col .= '</tr>';
	$cutoff = $ilconfig['globalfilters_auctiondescriptioncutoff'];
	$sql = $ilance->db->query("
		SELECT id, project_title, description, startprice, buynow_price, reserve_price, buynow_qty, buynow_qty_lot, project_details, filtered_auctiontype, cid, sample, currency, city, state, zipcode, country, attributes, sku, upc, partnumber, modelnumber, ean, keywords, project_type, project_state, dateupload, correct, user_id, rfpid, sample_uploaded, bulk_id
		FROM " . DB_PREFIX . "bulk_tmp
		WHERE bulk_id = '" . intval($bulk_id) . "'
		LIMIT " . $ilconfig['globalfilters_bulkuploadpreviewlimit']
	);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$valid = $ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']);
			if (!empty($valid))
			{
				$style = '';
			}
			else
			{
				$style = 'background-color:#ffcccc';
			}
			$col .= '<tr valign="top" style="' . $style . '" class="alt1">';
			$col .= '<td>' . shorten(handle_input_keywords($res['project_title']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['description']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords(sprintf("%01.2f", $res['startprice'])), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords(sprintf("%01.2f", $res['buynow_price'])), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords(sprintf("%01.2f", $res['reserve_price'])), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['buynow_qty']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['buynow_qty_lot']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['project_details']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['filtered_auctiontype']), $cutoff) . '</td>';
			$col .= '<td><div style="padding-top:3px">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res['cid']) . ' (#' . handle_input_keywords($res['cid']) . ')</div></td>';
			$pos = strpos($res['sample'], "|");
			if ($pos)
			{
				$pictures = explode('|', $res['sample']);
				$picture = $pictures[0];
			}
			else
			{
				$picture = $res['sample'];
			}
			$col .= (empty($picture))
				? '<td align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" border="0" alt="" id="" /></td>'
				: '<td align="center"><img src="' . handle_input_keywords($picture) . '" width="60" height="50" border="0" alt="" id="" /></td>';
			if ($ilconfig['globalserverlocale_currencyselector'])
			{
				$col .= (empty($res['currency'])) ? '<td>-</td>' : '<td>' . shorten(handle_input_keywords($res['currency']), $cutoff) . '</td>';
			}
			$col .= '<td>' . shorten(handle_input_keywords($res['city']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['state']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['zipcode']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['country']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['attributes']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['sku']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['upc']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['partnumber']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['modelnumber']), $cutoff) . '</td>';
			$col .= '<td>' . shorten(handle_input_keywords($res['ean']), $cutoff) . '</td>';
			$col .= '</tr>';
		}
	}
	$preview_count = ($items > $ilconfig['globalfilters_bulkuploadpreviewlimit']) ? $ilance->language->construct_phrase('{_showing_only_x_results_for_this_preview}', $ilconfig['globalfilters_bulkuploadpreviewlimit']): '';
	$col .= '</table><div style="clear:both"></div>';
}
// #### ASSIGN PREVIEW #########################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'assign-preview')
{
	$show['widescreen'] = true;
	$navcrumb = array();
	$navcrumb[HTTP_SERVER . "$ilpage[bulk]?cmd=sell"] = '{_sell}';
	$navcrumb[HTTP_SERVER . "$ilpage[bulk]?cmd=sell"] = '{_bulk}';
	$navcrumb[""] = '{_preview}';

	// #### duration code logic ####################################
	$duration = isset($ilance->GPC['duration']) ? intval($ilance->GPC['duration']) : '';
	$duration = $ilance->auction_post->duration($duration, 'duration', false, 'D', true, 0);
	$durationbits = isset($ilance->GPC['duration_unit']) ? intval($ilance->GPC['duration_unit']) : 'D';
	$durationbits = $ilance->auction_post->print_duration_logic($durationbits, 'duration_unit', false, 'duration', true, 0);

	// #### escrow filter (if enabled, javascript will hide the payment methods input box on preview also)
	$escrowfilter = $ilance->auction_post->print_escrow_filter(0, 'product', 'productmerchant', false);

	// #### shipping ###############################################
	$shipping = $ilance->auction_post->print_shipping_logic(false);

	// #### ship handling time and fee if applicable #######################
	$shipping_handling = $ilance->auction_post->print_ship_handling_logic(false);

	// #### item return policy #####################################
	$returnpolicy = $ilance->auction_post->print_return_policy(false);

	// #### public message boards? #################################
	$publicboard = $ilance->auction_post->print_public_board('filter_publicboard');

	// #### bid filters ############################################
	$bidfilters = $ilance->auction_post->print_bid_filters();

	// #### construct countries / states pulldown ##################
	$jscountry = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : $ilconfig['registrationdisplay_defaultcountry'];
	$jsstate = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : $ilconfig['registrationdisplay_defaultstate'];
	$jscity = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : $ilconfig['registrationdisplay_defaultcity'];

	$countryid = fetch_country_id($jscountry, $_SESSION['ilancedata']['user']['slng']);
	$country_js_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $jscountry, 'filtered_country', false, 'filtered_state');
	$state_js_pulldown = '<div id="stateid" style="height:20px">' . $ilance->common_location->construct_state_pulldown($countryid, $jsstate, 'filtered_state') . '</div>';
	$js_start = $ilance->auction_post->print_js('product', true);

	// #### sum of individual fees applied #########################
	$insertiontotal = $buynowtotal = $reservetotal = 0;

	// #### listing enhancements ###################################
	//$enhancements = $ilance->auction_post->print_listing_enhancements('product', 'bulk');

	$col = '<form name="ilform" method="post" action="' . HTTP_SERVER . $ilpage['bulk'] . '" accept-charset="UTF-8" style="margin:0px" onsubmit="return disable_submit_button(this, \'{_your_action_is_being_processed_allow_some_time}\', 1, \'working_icon\')">';
	$col .= '<input type="hidden" name="cmd" value="sell" />';
	$col .= '<input type="hidden" name="mode" value="bulk" />';
	$col .= '<input type="hidden" name="do" value="assign-import" />';
	$col .= '<input type="hidden" name="bulk_id" value="' . intval($ilance->GPC['bulk_id']) . '" />';
	$hidden = '';
	// #### column titles ##########################################
	$col .= '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
	$col .= '<tr class="alt2" valign="top">';
	if (isset($ilance->GPC['col']) AND !empty($ilance->GPC['col']) AND is_array($ilance->GPC['col']))
	{
		$empty = $notempty = 0;
		foreach ($ilance->GPC['col'] AS $key => $field)
		{
			if (empty($field))
			{
				$empty++;
			}
			else
			{
				$style = isset($columnstyle[$field]) ? $columnstyle[$field] : '';
				$col .= '<td nowrap="nowrap" style="' . $style . '"><div>' . $columnphrases["$field"] . '</div></td>';
				$identifier["$key"] = $field;
				$hidden .= '<input type="hidden" name="co[' . $key . ']" value="' . handle_input_keywords($field) . '" />' . "\n";
				$notempty++;
			}
		}
	}
	$col .= '</tr>';

	// #### seller must have required fields selected from the pulldown
	$passrequirement = false;
	if (isset($identifier) AND !empty($identifier) AND is_array($identifier))
	{
		if (in_array('project_title', $identifier) AND in_array('description', $identifier) AND in_array('startprice', $identifier) AND in_array('buynow_qty', $identifier) AND in_array('project_details', $identifier) AND in_array('cid', $identifier) AND in_array('filtered_auctiontype', $identifier) AND in_array('city', $identifier) AND in_array('state', $identifier) AND in_array('zipcode', $identifier) AND in_array('country', $identifier))
		{
			$passrequirement = true;
		}
	}
	if ($passrequirement == false)
	{
		print_notice('{_you_did_not_assign_required_pulldown_fields}', '{_sorry_you_must_reupload_your_csv_file_and_select_the_proper_columns}', HTTP_SERVER . $ilpage['bulk'] . '?cmd=sell', '{_back}');
		exit();
	}
	$bulkattachlimit = 0;
	
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'yes')
	{
		$bulkattachlimit = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bulkattachlimit');
	}
	$sql_file_sum = $ilance->db->query("
		SELECT SUM(filesize) AS attach_usage_total
		FROM " . DB_PREFIX . "attachment
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND bulk_id > 0
	");
	if ($ilance->db->num_rows($sql_file_sum) > 0)
	{
		$res_file_sum = $ilance->db->fetch_array($sql_file_sum, DB_ASSOC);
		$attach_usage_total = print_filesize($res_file_sum['attach_usage_total']);
	}
	else
	{
		$res_file_sum['attach_usage_total'] = 0;
	}
	$attach_usage_left = ($res_file_sum['attach_usage_total']) ? $res_file_sum['attach_usage_total'] : $bulkattachlimit;
	if ($res_file_sum['attach_usage_total'] > 0)
	{
		$attach_usage_left = ($bulkattachlimit - $res_file_sum['attach_usage_total']);
	}
	// #### results ################################################
	$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true);
	$sql_fields = 'id, project_title, description, startprice, buynow_price, reserve_price, buynow_qty, buynow_qty_lot, project_details, filtered_auctiontype, cid, sample';
	$sql_fields .= ($ilconfig['globalserverlocale_currencyselector']) ? ', currency' : '';
	$sql_fields .= ', city, state, zipcode, country, attributes, sku, upc, partnumber, modelnumber, ean, dateupload, correct, user_id, rfpid, sample_uploaded, bulk_id';
	$sql = $ilance->db->query("
		SELECT $sql_fields
		FROM " . DB_PREFIX . "bulk_tmp
		WHERE bulk_id = '" . intval($ilance->GPC['bulk_id']) . "'
	", 0, null, __FILE__, __LINE__);
	$itemuploadcount = $ilance->db->num_rows($sql);
	$z = $sumfees = 0;
	$td = $questions = array();
	while ($res = $ilance->db->fetch_array($sql, DB_BOTH))
	{
		$z++;
		$startingprice_error = $buynowprice_error = $reserveprice_error = $filtered_auctiontype_error = $photo_error = $categoryquestion_error = false;
		$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
		foreach ($identifier AS $key => $value)
		{
			if ($value == 'cid')
			{
				// determine if we can post into this selected category
				$canpost = $ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $res[$key]);
			}
			else if ($value == 'currency' AND $ilconfig['globalserverlocale_currencyselector'])
			{
				$currencyid = isset($ilance->currency->currencies[mb_strtoupper($res[$key])]['currency_id']) ? $ilance->currency->currencies[mb_strtoupper($res[$key])]['currency_id'] : $ilconfig['globalserverlocale_defaultcurrency'];
			}
		}
		$td['class'][$z] = 'alt1';
		$td['style'][$z] = 'background-color:#ffcccc';
		$fontclass1 = ($canpost) ? 'black' : 'red';
		$fontclass2 = ($canpost) ? 'black' : 'red';
		$fontclass3 = ($canpost) ? 'black' : 'red';
		$fontclass4 = ($canpost) ? 'black' : 'red';
		$fontclass5 = ($canpost) ? 'black' : 'red';
		foreach ($identifier AS $key => $value)
		{
			if ($value == 'project_title')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), $ilconfig['globalfilters_maxcharacterstitle'])) . '</td>';
			}
			else if ($value == 'description')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass2 . '">' . short_string(print_string_wrap(strip_tags($res[$key]), 50), $ilconfig['globalfilters_maxcharacterstitle']) . '</td>';
			}
			else if ($value == 'cid')
			{
				$cid = $res[$key];
				if ($res[$key] > 0)
				{
					if (!$canpost)
					{
						$td[$value][$z] = '<td valign="top"><span style="color:red">{_cannot_be_listed_in_this_category} (#<strong>' . $res[$key] . '</strong>)</span></td>';
					}
				}
				else
				{
					$td[$value][$z] = '<td><span style="color:red">{_unknown}- {_cannot_be_listed_in_this_category}</span></td>';
				}
			}
			else if ($value == 'startprice')
			{
				$startingprice = $res[$key];
				if ($res[$key] > 0)
				{
					//if ((isset($buynowprice) AND $buynowprice > 0 AND $startingprice >= $buynowprice) OR (isset($reserveprice) AND $reserveprice > 0 AND $startingprice >= $reserveprice))
					if ((isset($buynowprice) AND $buynowprice > 0 AND $startingprice > $buynowprice) OR (isset($reserveprice) AND $reserveprice > 0 AND $startingprice > $reserveprice))
					{
						$startingprice_error = true;
						$td[$value][$z] = '<td valign="top"><span class="red">' . $ilance->currency->format($ilance->currency->string_to_number($res[$key]), $currencyid) . '</span></td>';
					}
					else 
					{
						$td[$value][$z] = '<td valign="top"><span class="' . $fontclass3 . '">' . $ilance->currency->format($ilance->currency->string_to_number($res[$key]), $currencyid) . '</span></td>';
					}
				}
				else
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass3 . '">' . $ilance->currency->format('0.01', $currencyid) . '</span></td>';
				}
			}
			else if ($value == 'buynow_price')
			{
				$buynowprice = $res[$key];
				if ($res[$key] > 0)
				{
					$feebit = '';
					if ((isset($startingprice) AND $buynowprice < $startingprice) OR (isset($reserveprice) AND $reserveprice > 0 AND $buynowprice < $reserveprice))
					{
						$buynowprice_error = true;
						$td[$value][$z] = '<td valign="top"><span class="red">' . $ilance->currency->format($ilance->currency->string_to_number($res[$key]), $currencyid) . '</span>' . $feebit . '</td>';
					}
					else 
					{
						if ($ilconfig['productupsell_buynowcost'] > 0 AND $canpost)
						{
							$sumfees += $ilconfig['productupsell_buynowcost'];
							$feebit = '<div class="smaller gray" style="padding-top:3px">{_fee}: <span class="blue">' . $ilance->currency->format($ilconfig['productupsell_buynowcost']) . '</span></div>';
							$buynowtotal += $ilconfig['productupsell_buynowcost'];
						}
						$td[$value][$z] = '<td valign="top"><span class="' . $fontclass3 . '">' . $ilance->currency->format($ilance->currency->string_to_number($res[$key]), $currencyid) . '</span>' . $feebit . '</td>';
					}
				}
				else if ($res[$key] <= 0)
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass2 . '">{_none}</span></td>';
				}
			}
			else if ($value == 'reserve_price')
			{
				$reserveprice = $res[$key];
				if ($res[$key] > 0)
				{
					$feebit = '';
					if ((isset($startingprice) AND $reserveprice < $startingprice) OR (isset($buynowprice) AND $buynowprice > 0 AND $reserveprice > $buynowprice))
					{
						$reserveprice_error = true;
						$td[$value][$z] = '<td valign="top"><span class="red">' . $ilance->currency->format($ilance->currency->string_to_number($res[$key]), $currencyid) . '</span>' . $feebit . '</td>';
					}
					else 
					{
						if ($ilconfig['productupsell_reservepricecost'] > 0 AND $canpost)
						{
							$sumfees += $ilconfig['productupsell_reservepricecost'];
							$feebit = '<div class="smaller gray" style="padding-top:3px">{_fee}: <span class="blue">' . $ilance->currency->format($ilconfig['productupsell_reservepricecost']) . '</span></div>';
							$reservetotal += $ilconfig['productupsell_reservepricecost'];
						}
						$td[$value][$z] = '<td valign="top"><span class="' . $fontclass3 . '">' . $ilance->currency->format($ilance->currency->string_to_number($res[$key]), $currencyid) . '</span>' . $feebit . '</td>';
					}
				}
				else if ($res[$key] <= 0)
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass2 . '">{_none}</span></td>';
				}
			}
			else if ($value == 'buynow_qty')
			{
				if ($res[$key] > 0)
				{
					if(isset($filtered_auctiontype) AND $filtered_auctiontype == 'regular')
					{
						$td[$value][$z] = '<td valign="top"><span class="' . $fontclass3 . '">1</span></td>';
					}
					else 
					{
						$td[$value][$z] = '<td valign="top"><span class="' . $fontclass3 . '">' . intval($res[$key]) . '</span></td>';
					}
				}
				else if ($res[$key] <= 0 OR $res[$key] == '')
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass2 . '">1</span></td>';
				}
			}
			else if ($value == 'buynow_qty_lot')
			{
				if ($res[$key] > 0)
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass3 . '">' . intval($res[$key]) . '</span></td>';
				}
				else if ($res[$key] <= 0 OR $res[$key] == '')
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass2 . '">0</span></td>';
				}
			}
			else if ($value == 'project_details')
			{
				if ($res[$key] != '')
				{
					if (mb_strrchr($res[$key], 'realtime'))
					{
						$temp = explode('|', $res[$key]);
						$td[$value][$z] = '<td valign="top"><span style="color:' . $fontclass4 . '">{_scheduled}:</span><div style="padding-top:3px; color:' . $fontclass4 . '" class="smaller">' . print_date($temp[1]) . '</div></td>';
					}
					else
					{
						$td[$value][$z] = '<td valign="top"><span style="color:' . $fontclass4 . '">{_public}</span></td>';
					}
				}
				else
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass2 . '">{_public}</span></td>';
				}
			}
			else if ($value == 'filtered_auctiontype')
			{
				$filtered_auctiontype = $res[$key];
				if ($res[$key] == 'regular')
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass2 . '">' . strip_tags(ucfirst($res[$key])) . '</span></td>';
					
					$td['buynow_qty'][$z] = '<td valign="top"><span class="' . $fontclass3 . '">1</span></td>';
				}
				elseif ($res[$key] == 'fixed')
				{
					$td[$value][$z] = '<td valign="top"><span class="' . $fontclass2 . '">' . strip_tags(ucfirst($res[$key])) . '</span></td>';
					
					$startingprice_error = false;
					$td['startprice'][$z] = '<td valign="top"><span class="' . $fontclass3 . '">{_none}</span></td>';
					$reserveprice_error = false;
					$td['reserve_price'][$z] = '<td valign="top"><span class="' . $fontclass2 . '">{_none}</span></td>';
				}
				else
				{
					$filtered_auctiontype_error = true; 
					$td[$value][$z] = '<td valign="top"><span class="red">' . strip_tags(ucfirst($res[$key])) . '</span></td>';
				}
			}
			else if ($value == 'sample')
			{
				$td[$value][$z] = '<td align="center" valign="top"><div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" border="0" alt="" id="" width="60" height"50" /></div></td>';
				if (empty($res[$key]))
				{
					if ($ilconfig['attachment_forceproductupload'] == '1')
					{
						$photo_error = true;
					}
				}
				else
				{
					$sample = $res[$key];
					$pos = strpos($sample, "|");
					if ($pos)
					{
						$pictures = explode('|', $sample);
					}
					else
					{
						$pictures['0'] = $sample;
					}
					foreach ($pictures AS $pickey => $picvalue)
					{
						if ($pickey == $ilconfig['attachmentlimit_slideshowmaxfiles'])
						{
							break;
						}
						if ($ilconfig['attachment_forceproductupload'] == '1')
						{
							if ($bulkattachlimit == '-1')
							{
								//$size = getimagesize($picvalue);
								//$size = is_array($size) ? 1 : false;
								//$size = $ilance->attachment_tools->get_remote_file_size($picvalue, false);
								$size = 1;
							}
							else 
							{
								$size = $ilance->attachment_tools->get_remote_file_size($picvalue, false);
							}
						}
						else 
						{
							$size = 1;
						}
						if ($size == false)
						{
							if ($pickey == '0')
							{
								//set sample uploaded to 2 means this is incorrect link
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "bulk_tmp
									SET sample_uploaded = '2'
									WHERE id = '" . intval($res['0']) . "'
								");
								if ($ilconfig['attachment_forceproductupload'] == '1')
								{
									$photo_error = true;
								}
								break;
							}
						}
						else 
						{
							if (!$photo_error)
							{
								if ($bulkattachlimit == '-1')
								{
									if ($pickey == '0')
									{
										$td[$value][$z] = '<td align="center" valign="top"><img src="' . $picvalue . '" width="60" height="50" border="0" alt="" id="" /></td>';
									}
								}
								else if (($attach_usage_left - $size) > 0)
								{
									$attach_usage_left = $attach_usage_left - $size;
									if ($pickey == '0')
									{
										$td[$value][$z] = '<td align="center" valign="top"><img src="' . $picvalue . '" width="60" height="50" border="0" alt="" id="" /></td>';
									}
								}
								else 
								{
									// set sample uploaded to 3 means user does not have required space for bulk attachment
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "bulk_tmp
										SET sample_uploaded = '3'
										WHERE id = '" . intval($res['0']) . "'
									");
									if ($ilconfig['attachment_forceproductupload'] == '1')
									{
										$photo_error = true;
									}
									break;
								}
							}
						}
					}
				}
			}
			else if ($value == 'currency' AND $ilconfig['globalserverlocale_currencyselector'])
			{
				if (empty($res[$key]))
				{
					$td[$value][$z] = '<td align="center" valign="top">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['currency_abbrev'] . '</td>';
				}
				else
				{
					$td[$value][$z] = '<td align="center" valign="top">' . mb_strtoupper($res[$key]) . '</td>';
				}
			}
			else if ($value == 'city')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
			else if ($value == 'state')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
			else if ($value == 'zipcode')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
			else if ($value == 'country')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
			else if ($value == 'attributes')
			{
				$output = '';
				$result = array();
				$arr = explode("|", $res[$key]);
				if (count($arr) > 0)
				{
					foreach ($arr as $arr_key => $arr_value)
					{
						$split = explode("=", $arr_value);
						if (count($split) > 1)
						{
							$result[$split[0]] = $split[1];
						}
					}
				}
				if (!isset($questions[$cid]))
				{
					$sql_questions = $ilance->db->query("SELECT questionid, required, question_" . $_SESSION['ilancedata']['user']['slng'] . " AS question FROM " . DB_PREFIX . "product_questions WHERE cid = '" . $cid . "' AND visible = '1'");
					if ($ilance->db->num_rows($sql_questions) > 0)
					{
						while($res_questions = $ilance->db->fetch_array($sql_questions, DB_ASSOC))
						{
							$questions[$cid][$res_questions['questionid']] = $res_questions;
						}
					}
				}
				if (isset($questions[$cid]) AND is_array($questions[$cid]))
				{
					foreach ($questions[$cid] as $questionid => $arr)
					{
						if (isset($result[$questionid]))
						{
							$output .= $questions[$cid][$questionid]['question'] . " (# " . $questionid . ") = " . $result[$questionid] . "<br />";
						}
						else
						{
							$class = '';
							if ($questions[$cid][$questionid]['required'] == '1')
							{
								$class = 'class="red"';
								$categoryquestion_error = true;
							}
							$output .= "<span " . $class . ">" . $questions[$cid][$questionid]['question'] . " (# " . $questionid . ") = </span><br />";
						}
					}
				}
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . $output . '</td>';
			}
			else if ($value == 'sku')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
			else if ($value == 'upc')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
			else if ($value == 'partnumber')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
			else if ($value == 'modelnumber')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
			else if ($value == 'ean')
			{
				$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
			}
		}
		if (isset($cid) AND isset($reserveprice) AND isset($startingprice) AND isset($buynowprice) AND $canpost)
		{
			// check for category insertion fees
			if ($reserveprice > $startingprice)
			{
				$startingprice = $reserveprice;
			}
			if ($buynowprice > $startingprice)
			{
				$startingprice = $buynowprice;
			}
			if ($ilconfig['globalserverlocale_currencyselector'] AND $currencyid != $ilconfig['globalserverlocale_defaultcurrency'])
			{
				$startingprice = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $startingprice, $currencyid);
			}
			$insertionfees = $ilance->accounting_fees->calculate_insertion_fee($cid, 'product', $startingprice, 0, $_SESSION['ilancedata']['user']['userid'], 0, 0, 0, false);
			$insertionfeesformatted = $feebit = '';
			if ($insertionfees > 0 AND !$photo_error)
			{
				$insertionfeesformatted = $ilance->currency->format($insertionfees);
				$feebit = '<div class="smaller" style="padding-top:3px">{_fee}: ' . $insertionfeesformatted . '</div>';
			}
			// convert category id number into actual category title..
			$td['cid'][$z] = '<td valign="top"><div>' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid) . ' (# ' . $cid . ')</div>' . $feebit . '</td>';
		}
		if ($canpost AND !$startingprice_error AND !$buynowprice_error AND !$reserveprice_error AND !$filtered_auctiontype_error AND !$photo_error AND !$categoryquestion_error)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "bulk_tmp
				SET correct = '1'
				WHERE id = '" . intval($res['0']) . "'
			");
			//$td['style'][$z] = 'background-color:#bffdbd';
			$td['style'][$z] = '';
			$disabled = 1;
			if ($insertionfees > 0)
			{
				$sumfees += $insertionfees;
				$insertiontotal += $insertionfees;
			}
		}
		else 
		{
			$sumfees = 0;
		}
		unset($startingprice, $buynowprice, $reserveprice, $filtered_auctiontype, $cid);
	}
	$limit = ($z <= $ilconfig['globalfilters_bulkuploadpreviewlimit']) ? $z : $ilconfig['globalfilters_bulkuploadpreviewlimit'];
	for ($a = 1; $a <= $limit; $a++)
	{
		$col .= '<tr class="' . $td['class'][$a] . '" style="' . $td['style'][$a] . '" valign="top">';
		foreach ($identifier AS $key => $value)
		{
			$col .= isset($td[$value][$a]) ? $td[$value][$a] : '';
		}
		$col .= '</tr>';
	}
	unset($z);
	$feesinsertiontax = $feesbuynowtax = $feesreservetax = 0;
	
	if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'insertionfee'))
	{
		$feesinsertiontax = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $insertiontotal, 'insertionfee', 0);
	}
	if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'enhancements'))
	{
		$feesbuynowtax = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $buynowtotal, 'enhancements', 0);
		$feesreservetax = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $reservetotal, 'enhancements', 0);
	}
	$col .= '</table>';
	$draft = (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft']) ? 'checked="checked"' : '';
	$saveasdraft = '<label for="savedraft"><input type="checkbox" id="savedraft" name="saveasdraft" value="1" ' . $draft . ' /> {_save_this_auction_as_a_draft}</label>';
	$disabled = isset($disabled) ? '' : 'disabled="disabled"';
	$fees_html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
<tr class="alt1">
	<td width="100%"></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td align="right" valign="top" nowrap="nowrap">
		<div style="padding-bottom:10px">{_picture_fees}</div>
		<div style="padding-bottom:10px">{_buy_now_fees}</div>
		<div style="padding-bottom:10px">{_reserve_price_fees}</div>
		<div style="padding-bottom:10px">{_insertion_fees}</div>
		<div style="padding-bottom:10px">{_sub_total}</div>
		<div style="padding-bottom:10px">{_tax}</div>
		<div><span style="font-size:16px"><strong>{_total}</strong></span></div>
	</td>
	<td align="right" valign="top" nowrap="nowrap">
		<div style="padding-bottom:10px">' . $ilance->currency->format(0) . '</div>
		<div style="padding-bottom:10px">' . $ilance->currency->format($buynowtotal) . '</div>
		<div style="padding-bottom:10px">' . $ilance->currency->format($reservetotal) . '</div>
		<div style="padding-bottom:10px">' . $ilance->currency->format($insertiontotal) . '</div>
		<div style="padding-bottom:10px;">' . $ilance->currency->format($buynowtotal + $reservetotal + $insertiontotal) . '</div>
		<div style="padding-bottom:10px">' . $ilance->currency->format($feesbuynowtax + $feesreservetax + $feesinsertiontax) . '</div>
		<div><span style="font-size:16px"><strong>' . $ilance->currency->format($buynowtotal + $reservetotal + $insertiontotal + $feesbuynowtax + $feesreservetax + $feesinsertiontax) . '</strong></span></div>
	</td>
</tr>
<tr class="alt2_top">
	<td colspan="11"><input type="submit" value=" {_continue} " style="font-size:15px" class="buttons"' . $disabled . ' id="submitbulk" /><span id="working_icon" style="padding-left:5px;display:none"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'working.gif" border="0" width="13" height="13" /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span class="blue"><a href="{ilpage[bulk]}?cmd=sell">{_back}</a></span></td>
</tr>
</table>';
}
// #### ASSIGN IMPORT ##########################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'assign-import')
{
	$navcrumb = array();
	$navcrumb[""] = '{_sell_bulk_items}';
	$bulk_id = isset($ilance->GPC['bulk_id']) ? intval($ilance->GPC['bulk_id']) : 0;
	$sql_fields = 'id, project_title, description, startprice, buynow_price, reserve_price, buynow_qty, buynow_qty_lot, project_details, filtered_auctiontype, cid, sample';
	$sql_fields .= ($ilconfig['globalserverlocale_currencyselector']) ? ', currency' : '';
	$sql_fields .= ', city, state, zipcode, country, attributes, sku, upc, partnumber, modelnumber, ean, dateupload, correct, user_id, rfpid, sample_uploaded, bulk_id';
	$sql = $ilance->db->query("
		SELECT $sql_fields
		FROM " . DB_PREFIX . "bulk_tmp
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND bulk_id = '" . $bulk_id . "'
			AND correct = '1'
	", 0, null, __FILE__, __LINE__);
	$itemuploadcount = $ilance->db->num_rows($sql);
	if (isset($ilance->GPC['co']) AND !empty($ilance->GPC['co']) AND is_array($ilance->GPC['co']))
	{
		$empty = $notempty = 0;
		foreach ($ilance->GPC['co'] AS $key => $field)
		{
			if (empty($field))
			{
				$empty++;
			}
			else
			{
				$identifier["$key"] = $field;
				$notempty++;
			}
		}
	}
	$area_title = '{_saving_bulk_item_import_data}<div class="smaller">{_adding}: <strong>' . count($itemuploadcount) . '</strong> {_items}</div>';
	$page_title = SITE_NAME . ' - {_saving_bulk_item_import_data}';
	$pids = array();
	$z = 1;
	$a = $b = 0;
	while ($res = $ilance->db->fetch_array($sql, DB_BOTH))
	{
		foreach ($identifier AS $key => $value)
		{
			switch ($value)
			{
				case 'project_title':
				{
					$ilance->GPC['project_title'] = cutstring($res[$key], $ilconfig['globalfilters_maxcharacterstitle']);
					break;
				}
				case 'description':
				{
					$ilance->GPC['description'] = $ilance->GPC['bbeditor_html_ouput_bbeditor'] = substr($res[$key], 0, intval($ilconfig['globalfilters_maxcharactersdescriptionbulk']));
					break;
				}
				case 'startprice':
				{
					$ilance->GPC['startprice'] = (isset($res[$key]) AND $res[$key] > 0) ? $ilance->currency->string_to_number($res[$key]) : '0.01';
					break;
				}
				case 'buynow_price':
				{
					$ilance->GPC['buynow_price'] = (isset($res[$key]) AND $res[$key] > 0) ? $ilance->currency->string_to_number($res[$key]) : '0';
					$ilance->GPC['buynow'] = ($res[$key] > 0) ? '1' : '0';
					break;
				}
				case 'reserve_price':
				{
					$ilance->GPC['reserve_price'] = ($res[$key] > 0) ? $ilance->currency->string_to_number($res[$key]) : '0';
					$ilance->GPC['reserve'] = ($res[$key] > 0) ? '1' : '0';
					break;
				}
				case 'buynow_qty':
				{
					$ilance->GPC['buynow_qty'] = (isset($res[$key])) ? intval($res[$key]) : '1';
					break;
				}
				case 'buynow_qty_lot':
				{
					$ilance->GPC['buynow_qty_lot'] = (isset($res[$key])) ? intval($res[$key]) : '0';
					$ilance->GPC['items_in_lot'] = $ilance->GPC['buynow_qty_lot'] > 1 ? $ilance->GPC['buynow_qty_lot'] : 0;
					$ilance->GPC['buynow_qty_lot'] = $ilance->GPC['buynow_qty_lot'] > 1 ? 1 : 0;
					break;
				}
				case 'project_details':
				{
					$ilance->GPC['project_details'] = (isset($res[$key])) ? ($res[$key]) : 'public';
					break;
				}
				case 'filtered_auctiontype':
				{
					$ilance->GPC['filtered_auctiontype'] = (isset($res[$key])) ? ($res[$key]) : 'regular';
					break;
				}
				case 'cid':
				{
					$ilance->GPC['cid'] = (isset($res[$key])) ? intval($res[$key]) : '0';
					break;
				}
				case 'sample':
				{
					$ilance->GPC['image'] = (isset($res[$key])) ? strip_tags($res[$key]) : '';
					break;
				}
				case 'currency':
				{
					$ilance->GPC['currency'] = (isset($res[$key])) ? $res[$key] : '';
					break;
				}
				case 'city':
				{
					$ilance->GPC['city'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'state':
				{
					$ilance->GPC['state'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'zipcode':
				{
					$ilance->GPC['zipcode'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'country':
				{
					$ilance->GPC['country'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'attributes':
				{
					$ilance->GPC['attributes'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'sku':
				{
					$ilance->GPC['sku'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'upc':
				{
					$ilance->GPC['upc'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'partnumber':
				{
					$ilance->GPC['partnumber'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'modelnumber':
				{
					$ilance->GPC['modelnumber'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
				case 'ean':
				{
					$ilance->GPC['ean'] = (isset($res[$key])) ? ($res[$key]) : '';
					break;
				}
			}
		}
		$ilance->GPC['rfpid'] = $ilance->auction_rfp->construct_new_auctionid_bulk();
		$pids[] = $ilance->GPC['rfpid'];
		// #### BIDDING FILTERS ########################################
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
		$ilance->GPC['filter_escrow'] = isset($ilance->GPC['filter_escrow']) ? intval($ilance->GPC['filter_escrow']) : '0';
		$ilance->GPC['filter_gateway'] = isset($ilance->GPC['filter_gateway']) ? intval($ilance->GPC['filter_gateway']) : '0';
		$ilance->GPC['filter_ccgateway'] = isset($ilance->GPC['filter_ccgateway']) ? intval($ilance->GPC['filter_ccgateway']) : '0';
		$ilance->GPC['filter_offline'] = isset($ilance->GPC['filter_offline']) ? intval($ilance->GPC['filter_offline']) : '0';
		$ilance->GPC['paymethod'] = (isset($ilance->GPC['paymethod']) AND $ilance->GPC['filter_offline'] != '0') ? $ilance->GPC['paymethod'] : array();
		$ilance->GPC['paymethodcc'] = (isset($ilance->GPC['paymethodcc']) AND $ilance->GPC['filter_ccgateway'] != '0') ? $ilance->GPC['paymethodcc'] : array();
		$ilance->GPC['paymethodoptions'] = (isset($ilance->GPC['paymethodoptions']) AND $ilance->GPC['filter_gateway'] != '0') ? $ilance->GPC['paymethodoptions'] : array();
		$ilance->GPC['paymethodoptionsemail'] = (isset($ilance->GPC['paymethodoptionsemail']) AND $ilance->GPC['filter_gateway'] != '0') ? $ilance->GPC['paymethodoptionsemail'] : array();
		// ### OTHER DETAILS ###########################################
		$ilance->GPC['project_type'] = 'forward';
		$ilance->GPC['project_state'] = 'product';
		$ilance->GPC['additional_info'] = isset($ilance->GPC['additional_info']) ? $ilance->GPC['additional_info'] : '';
		$ilance->GPC['keywords'] = isset($ilance->GPC['keywords']) ? $ilance->GPC['keywords'] : '';
		$ilance->GPC['status'] = 'open';
		$ilance->GPC['bid_details'] = 'open';
		// #### RETURN POLICIES ########################################
		$ilance->GPC['returnaccepted'] = isset($ilance->GPC['returnaccepted']) ? intval($ilance->GPC['returnaccepted']) : '0';
		$ilance->GPC['returnwithin'] = isset($ilance->GPC['returnwithin']) ? intval($ilance->GPC['returnwithin']) : '0';
		$ilance->GPC['returngivenas'] = isset($ilance->GPC['returngivenas']) ? $ilance->GPC['returngivenas'] : 'none';
		$ilance->GPC['returnshippaidby'] = isset($ilance->GPC['returnshippaidby']) ? $ilance->GPC['returnshippaidby'] : 'none';
		$ilance->GPC['returnpolicy'] = isset($ilance->GPC['returnpolicy']) ? $ilance->GPC['returnpolicy'] : '';
		// #### SAVE AS DRAFT ##########################################
		$ilance->GPC['draft'] = '0';
		if (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft'])
		{
			$ilance->GPC['draft'] = '1';
			$ilance->GPC['status'] = 'draft';
		}
		// #### CUSTOM INFORMATION #####################################
		$ilance->GPC['custom'] = '';
		$attributes = isset($ilance->GPC['attributes']) ? trim($ilance->GPC['attributes']) : '';
		if (!empty($attributes))
		{
			$ilance->GPC['custom'] = $ilance->auction_rfp->convert_bulk_attributes_into_custom($attributes);
		}
		$ilance->GPC['custom'] = (!empty($ilance->GPC['custom']) ? $ilance->GPC['custom'] : array());
		$ilance->GPC['profileanswer'] = (!empty($ilance->GPC['profileanswer']) ? $ilance->GPC['profileanswer'] : array());
		$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
		// #### SCHEDULED AUCTION ONLY #################################
		if (stristr($ilance->GPC['project_details'], 'realtime') AND strlen($ilance->GPC['project_details']) == 28)
		{
			$datetimet = explode('|', $ilance->GPC['project_details']);
			$datetime = $datetimet[1];
			$datet = explode(' ', $datetime);
			$time = $datet[1];
			$date = $datet[0];
			$dateline = explode('-', $date);
			$ilance->GPC['month'] = $dateline[1];
			$ilance->GPC['year'] = $dateline[0];
			$ilance->GPC['day'] = $dateline[2];
			$timeline = explode(':', $time);
			$ilance->GPC['hour'] = $timeline[0];
			$ilance->GPC['min'] = $timeline[1];
			$ilance->GPC['sec'] = $timeline[2];
			unset($datetime, $time, $date, $datet, $datetimet, $dateline, $timeline);
			$ilance->GPC['project_details'] = 'realtime';
		}
		$ilance->GPC['year'] = (isset($ilance->GPC['year'])) ? $ilance->GPC['year'] : '';
		$ilance->GPC['month'] = (isset($ilance->GPC['month'])) ? $ilance->GPC['month'] : '';
		$ilance->GPC['day'] = (isset($ilance->GPC['day'])) ? $ilance->GPC['day'] : '';
		$ilance->GPC['hour'] = (isset($ilance->GPC['hour'])) ? $ilance->GPC['hour'] : '';
		$ilance->GPC['min'] = (isset($ilance->GPC['min'])) ? $ilance->GPC['min'] : '';
		$ilance->GPC['sec'] = (isset($ilance->GPC['sec'])) ? $ilance->GPC['sec'] : '';
		$ilance->GPC['invitelist'] = (isset($ilance->GPC['invitelist'])) ? $ilance->GPC['invitelist'] : '';
		$ilance->GPC['invitemessage'] = (isset($ilance->GPC['invitemessage'])) ? $ilance->GPC['invitemessage'] : '';
		$ilance->GPC['description_videourl'] = (isset($ilance->GPC['description_videourl'])) ? strip_tags($ilance->GPC['description_videourl']) : '';
		$ilance->GPC['sample'] = (isset($ilance->GPC['sample'])) ? strip_tags($ilance->GPC['sample']) : '';
		$ilance->GPC['image'] = (isset($ilance->GPC['image'])) ? $ilance->GPC['image'] : '';
		$ilance->GPC['buynow_qty'] = ($ilance->GPC['buynow'] == '1' AND $ilance->GPC['filtered_auctiontype'] == 'fixed') ? $ilance->GPC['buynow_qty'] : 1;
		// #### SHIPPING INFORMATION ###################################
		$shipping1 = array(
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
		// #### ITEM LOCATION INFORMATION ##############################
		$ilance->GPC['city'] = !empty($ilance->GPC['city']) ? $ilance->GPC['city'] : $_SESSION['ilancedata']['user']['city'];
		$ilance->GPC['state'] = !empty($ilance->GPC['state']) ? $ilance->GPC['state'] : $_SESSION['ilancedata']['user']['state'];
		$ilance->GPC['zipcode'] = !empty($ilance->GPC['zipcode']) ? $ilance->GPC['zipcode'] : $_SESSION['ilancedata']['user']['postalzip'];
		$ilance->GPC['country'] = !empty($ilance->GPC['country']) ? $ilance->GPC['country'] : $_SESSION['ilancedata']['user']['country'];
		$ilance->GPC['currencyid'] = ((empty($ilance->GPC['currency']) OR !isset($ilance->GPC['currency'])) ? $ilconfig['globalserverlocale_defaultcurrency'] : $ilance->db->fetch_field(DB_PREFIX . "currency", "currency_abbrev = '" . $ilance->db->escape_string($ilance->GPC['currency']) . "'", "currency_id"));
		// #### DETAILED ITEM INFORMATION ##############################
		$ilance->GPC['sku'] = (isset($ilance->GPC['sku'])) ? $ilance->GPC['sku'] : '';
		$ilance->GPC['upc'] = (isset($ilance->GPC['upc'])) ? $ilance->GPC['upc'] : '';
		$ilance->GPC['partnumber'] = (isset($ilance->GPC['partnumber'])) ? $ilance->GPC['partnumber'] : '';
		$ilance->GPC['modelnumber'] = (isset($ilance->GPC['modelnumber'])) ? $ilance->GPC['modelnumber'] : '';
		$ilance->GPC['ean'] = (isset($ilance->GPC['ean'])) ? $ilance->GPC['ean'] : '';
		$apihookcustom = array();

		($apihook = $ilance->api('selling_submit_bulk_end')) ? eval($apihook) : false;

		// #### CREATE AUCTION #########################################
		$ilance->auction_rfp->insert_product_auction(
			$_SESSION['ilancedata']['user']['userid'],
			$ilance->GPC['project_type'],
			$ilance->GPC['status'],
			$ilance->GPC['project_state'],
			$ilance->GPC['cid'],
			$ilance->GPC['rfpid'],
			$ilance->GPC['project_title'],
			$ilance->GPC['description'],
			$ilance->GPC['description_videourl'],
			$ilance->GPC['additional_info'],
			$ilance->GPC['keywords'],
			$ilance->GPC['custom'],
			$ilance->GPC['profileanswer'],
			$ilance->GPC['filtered_auctiontype'],
			$ilance->GPC['startprice'],
			$ilance->GPC['project_details'],
			$ilance->GPC['bid_details'],
			$ilance->GPC['filter_rating'],
			$ilance->GPC['filter_country'],
			$ilance->GPC['filter_state'],
			$ilance->GPC['filter_city'],
			$ilance->GPC['filter_zip'],
			$ilance->GPC['filter_businessnumber'],
			$ilance->GPC['filtered_rating'],
			$ilance->GPC['filtered_country'],
			$ilance->GPC['filtered_state'],
			$ilance->GPC['filtered_city'],
			$ilance->GPC['filtered_zip'],
			$ilance->GPC['city'],
			$ilance->GPC['state'],
			$ilance->GPC['zipcode'],
			$ilance->GPC['country'],
			$ilance->GPC['shipping'],
			$ilance->GPC['buynow'],
			$ilance->GPC['buynow_price'],
			$ilance->GPC['buynow_qty'],
			$ilance->GPC['buynow_qty_lot'],
			$ilance->GPC['items_in_lot'],
			$ilance->GPC['enhancements'],
			$ilance->GPC['reserve'],
			$ilance->GPC['reserve_price'],
			$ilance->GPC['filter_underage'],
			$ilance->GPC['filter_escrow'],
			$ilance->GPC['filter_gateway'],
			$ilance->GPC['filter_ccgateway'],
			$ilance->GPC['filter_offline'],
			$ilance->GPC['filter_publicboard'],
			$ilance->GPC['invitelist'],
			$ilance->GPC['invitemessage'],
			$ilance->GPC['year'],
			$ilance->GPC['month'],
			$ilance->GPC['day'],
			$ilance->GPC['hour'],
			$ilance->GPC['min'],
			$ilance->GPC['sec'],
			$ilance->GPC['duration'],
			$ilance->GPC['duration_unit'],
			$ilance->GPC['paymethod'],
			$ilance->GPC['paymethodcc'],
			$ilance->GPC['paymethodoptions'],
			$ilance->GPC['paymethodoptionsemail'],
			$ilance->GPC['draft'],
			$ilance->GPC['returnaccepted'],
			$ilance->GPC['returnwithin'],
			$ilance->GPC['returngivenas'],
			$ilance->GPC['returnshippaidby'],
			$ilance->GPC['returnpolicy'],
			$donation = 0,
			$charityid = 0,
			$donationpercentage = 0,
			$skipemailprocess = 1,
			$apihookcustom,
			true,
			$ilance->GPC['sample'],
			$ilance->GPC['currencyid'],
			$classified_price = 0, // todo: add to csv
			$classified_phone = '', // todo: add to csv
			$ilance->GPC['sku'],
			$ilance->GPC['upc'],
			$ilance->GPC['ean'],
			$ilance->GPC['partnumber'],
			$ilance->GPC['modelnumber'],
			$salestaxstate = '', // todo: add to csv
			$salestaxrate = '0', // todo: add to csv
			$salestaxshipping = '0' // todo: add to csv
		);
		$items = $ilance->db->fetch_field(DB_PREFIX . "projects", "", "count(id)");
		if ($items > $a)
		{
			$a = $items;
			$b++;
		}
		$z++;
		$sql2 = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql2) == 1)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "bulk_tmp
				SET rfpid = '" . intval($ilance->GPC['rfpid']) . "',
				sample = '" . $ilance->db->escape_string($ilance->GPC['image']) . "'
				WHERE id = '" . $res['id'] . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET bulkid = '" . intval($bulk_id) . "'
				WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
			", 0, null, __FILE__, __LINE__);
		}
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "bulk_sessions
		SET itemsuploaded = '" . intval($b) . "'
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND id = '" . $bulk_id . "'
	", 0, null, __FILE__, __LINE__);
	$posted_auctions = $b;
	$wrong_photo_auctions = $no_space_photo_auctions = 0;
	$sql2 = $ilance->db->query("
		SELECT sample_uploaded
		FROM " . DB_PREFIX . "bulk_tmp 
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
			AND bulk_id = '" . $bulk_id . "' 
			AND correct = '0'
	", 0, null, __FILE__, __LINE__);
	$incorrect_auctions = $ilance->db->num_rows($sql2);
	if ($incorrect_auctions > 0)
	{
		while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
		{
			if ($res2['sample_uploaded'] == '2')
			{
				$wrong_photo_auctions++;
			}
			else if($res2['sample_uploaded'] == '3')
			{
				$no_space_photo_auctions++;
			}
		}
	}
	// #### generate insertion fees for this bulk upload ###########
	$ilance->auction_fee->process_insertion_fee_transaction(0, 'product', $ilance->GPC['fees']['insertion'], 0, $_SESSION['ilancedata']['user']['userid'], 0, 0, true, $pids, $ilconfig['globalserverlocale_defaultcurrency']);
	$ilance->auction_fee->process_listing_bulk_enhancements_transaction($pids, ($ilance->GPC['fees']['buynow'] + $ilance->GPC['fees']['reserve']), $_SESSION['ilancedata']['user']['userid']);
	$hiddeninput = array(
		'incorrect_auctions' => $incorrect_auctions,
		'posted_auctions' => $posted_auctions,
		'wrong_photo_auctions' => $wrong_photo_auctions,
		'no_space_photo_auctions' => $no_space_photo_auctions
	);
	$crypted = encrypt_url($hiddeninput);
	refresh(HTTPS_SERVER . $ilpage['bulk'] . '?cmd=sell&do=bulk_complete&crypted=' . $crypted);
	exit();
}
// #### BULK COMPLETE ##########################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'bulk_complete')
{
	$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
	$url = '';
	$incorrect_auctions = isset($uncrypted['incorrect_auctions']) ? $uncrypted['incorrect_auctions'] : 0;
	$posted_auctions = isset($uncrypted['posted_auctions']) ? $uncrypted['posted_auctions'] : 0;
	$wrong_photo_auctions = isset($uncrypted['wrong_photo_auctions']) ? $uncrypted['wrong_photo_auctions'] : 0;
	$no_space_photo_auctions = isset($uncrypted['no_space_photo_auctions']) ? $uncrypted['no_space_photo_auctions'] : 0;
	$pprint_array = array('no_space_photo_auctions','wrong_photo_auctions','incorrect_auctions','posted_auctions','url','session_project_title','session_description','session_additional_info','session_budget','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id');
	$ilance->template->fetch('main', 'listing_forward_auction_bulk_complete.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### CATEGORYLIST ###########################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'categorylist')
{
	$ilconfig['globalauctionsettings_catcutoff'] = '100';
	$ilconfig['globalauctionsettings_newicondays'] = 0;
	$ilconfig['globalfilters_enablecategorycount'] = 0;
	$ilconfig['globalauctionsettings_catmapgenres'] = 0;
	$ilance->categories_parser->showcatid = true;
	if (($categoryresults = $ilance->cache->fetch('bulk_categorymap_4col_showcatid_' . $_SESSION['ilancedata']['user']['slng'])) === false)
	{
		$categoryresults = $ilance->categories_parser->print_subcategory_columns(4, 'product', 1, $_SESSION['ilancedata']['user']['slng'], 0, '', 0, 1, 'font-weight: bold;font-size:15px', 'font-weight: normal;', 10, '', false, true);
		$ilance->cache->store('bulk_categorymap_4col_showcatid_' . $_SESSION['ilancedata']['user']['slng'], $categoryresults);
	}
	$pprint_array = array('categoryresults');

	($apihook = $ilance->api('main_bulk_categorylist')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'bulk_categorylist.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### CATEGORYLIST ###########################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'downloadsample')
{
	$samplefile = '"project_title","description","startprice","buynow_price","reserve_price","buynow_qty","buynow_qty_lot","project_details","filtered_auctiontype","cid","sample","currency","city","state","zipcode","country","attributes"' . LINEBREAK . '"Shelby Cobra 427","For sale is a Factory Built 1967 AC Cobra with a real 427 Ford Power Top Loader 4 Speed. 9 IN. Rear. Only 3,050 miles since built. Shell Valley Body and Chassis. Currently titled in PA as a 1966. You will not be dissapointed! Lowest price AC Cobra with a real 427 on the internet. Amazing performance. Heater. Convertible Top.","10000.00","45000.00","40000.00","1","0","public","regular","646","http://americanclassicars.com/wp-content/uploads/2010/10/427Cobra1.jpg","USD","Toronto","Ontario","M3N2J9","Canada","131=Ford"' . LINEBREAK;
	$ilance->common->download_file($samplefile, "cars-trucks-646.csv", "text/plain");
	exit(); 
}
$csv_sep = $ilconfig['globalfilters_bulkuploadcolsep'];
$csv_encap = $ilconfig['globalfilters_bulkuploadcolencap'];
$pprint_array = array('csv_encap','csv_sep','description_limit','fees_html','bulk_id','disabled','preview_count','charityid','js_start','escrowfilter','shipping','shipping_handling','returnpolicy','publicboard','bidfilters','country_js_pulldown','state_js_pulldown','hidden','feestotal','feesbuynow','feesreserve','feesinsertion','sumfees','itemuploadcount','enhancements','coldata','data','duration','durationbits','feetotal','col','response','title','categoryresults','wantads_category_pulldown','two_column_categories','js','two_column_product_categories','prevnext', 'draft', 'saveasdraft');

($apihook = $ilance->api('main_bulk')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'bulk.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>	