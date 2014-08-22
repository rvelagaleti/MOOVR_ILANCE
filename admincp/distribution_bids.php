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
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-all-categories' AND isset($ilance->GPC['title']))
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET incrementgroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
		WHERE cattype = 'product'
	");
	
	print_action_success('{_all_categories_have_been_assigned_to_the_selected_bid_increment_group}', $ilpage['distribution'] . '?cmd=bids');
	exit();
}
// #### admin retracting or physically deleting bids ###################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'do-bid-action')
{
	if (isset($ilance->GPC['bidid']) AND is_array($ilance->GPC['bidid']) AND count($ilance->GPC['bidid']) > 0)
	{
		foreach ($ilance->GPC['bidid'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT bid_id, project_id, bidstatus, user_id, project_user_id, date_added, date_awarded, date_updated
				FROM " . DB_PREFIX . "project_realtimebids
				WHERE id = '" . intval($value) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				if (isset($ilance->GPC['retract']))
				{
					$res['isawarded'] = ($res['bidstatus'] == 'awarded') ? true : false;
					$res['reason'] = (!empty($ilance->GPC['bidretractreason'])) ? ilance_htmlentities($ilance->GPC['bidretractreason']) : 'Bid retracted by admin';
					// re-adjust the current bid amount due to the retraction
					$project_state = fetch_auction('project_state', intval($res['project_id']));
					if ($project_state == 'service')
					{
						$ilance->bid_retract->construct_service_bid_retraction($res['user_id'], intval($value), $res['project_id'], $res['reason'], $res['isawarded'], true);
					}
					else 
					{
						$ilance->bid_retract->construct_product_bid_retraction($res['user_id'], intval($value), $res['project_id'], $res['reason'], $res['isawarded'], true);
					}	
				}
				else if (isset($ilance->GPC['delete']))
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "project_realtimebids
						WHERE id = '" . intval($value) . "'
						LIMIT 1
					");
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "project_bids
						WHERE bid_id='".$res['bid_id']."' 
							AND project_id='".$res['project_id']."'
							AND project_user_id='".$res['project_user_id']."'
							AND user_id='".$res['user_id']."'
							AND date_added='".$res['date_added']."'
							AND date_awarded='".$res['date_awarded']."'
							AND date_updated='".$res['date_updated']."'
						LIMIT 1
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET bids = bids - 1
						WHERE project_id = '" . $res['project_id'] . "'
					");
				}
			}
		}
		print_action_success('{_selected_bids_have_been_successfully_removed_from_the_auction_listing}', $ilance->GPC['return']);
		exit();
	}
}
$area_title = '{_bid_manager}';
$page_title = SITE_NAME . ' - {_bid_manager}';

($apihook = $ilance->api('admincp_bid_management')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=bids', $_SESSION['ilancedata']['user']['slng']);

// #### update bid increment display order #############################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-increment-sort')
{
	if (!empty($ilance->GPC['sort']))
	{
		foreach ($ilance->GPC['sort'] AS $incrementid => $sortvalue)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "increments
				SET sort = '" . intval($sortvalue) . "'
				WHERE incrementid = '" . intval($incrementid) . "'
				LIMIT 1
			");
		}
	}
	refresh($ilpage['distribution'] . '?cmd=bids');
	exit();
}
// #### update bid field sorting ###############################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fields-sort')
{
	foreach ($ilance->GPC['sort'] AS $key => $value)
	{
		if (!empty($key) AND !empty($value))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "bid_fields
				SET sort = '" . $ilance->db->escape_string($value) . "'
				WHERE fieldid = '" . $ilance->db->escape_string($key) . "'
			");    
		}
	}
	print_action_success('{_custom_bid_field_sorting_has_been_updated_and_changes_should_take_effect_immediately}', $ilance->GPC['return']);
	exit();
}
// #### update custom bid field ################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatebidfield' AND isset($ilance->GPC['fieldid']) AND $ilance->GPC['fieldid'] > 0)
{
	$ilance->GPC['visible'] = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : 0;
	$query1 = $query2 = '';
	if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['question'] AS $slng => $value)
		{
			$query1 .= "`question_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
		}
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "`description_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "bid_fields
		SET 
		$query1
		$query2
		inputtype = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
		multiplechoice = '" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
		sort = '" . intval($ilance->GPC['sort']) . "',
		visible = '" . intval($ilance->GPC['visible']) . "'
		WHERE fieldid = '" . intval($ilance->GPC['fieldid']) . "'
	");
	print_action_success('{_custom_bid_field_was_updated_and_changes_should_take_effect_immediately}', $ilance->GPC['return']);
	exit();
}
// #### insert custom bid field ################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'addbidfield')
{
	$ilance->GPC['visible'] = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : 0;
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "bid_fields
		(fieldid, inputtype, multiplechoice, sort, visible)
		VALUES (
		NULL,
		'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
		'" . intval($ilance->GPC['sort']) . "',
		'" . intval($ilance->GPC['visible']) . "')
	");
	$insid = $ilance->db->insert_id();
	$query1 = $query2 = '';
	if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['question'] AS $slng => $value)
		{
			$query1 .= "`question_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
		}
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "`description_" . mb_strtolower($slng) . "` = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "bid_fields
		SET
		$query1
		$query2
		visible = '" . intval($ilance->GPC['visible']) . "'
		WHERE fieldid = '" . $insid . "'
		LIMIT 1
	");
	print_action_success('{_custom_bid_field_was_added_and_changes_should_take_effect_immediately}', $ilance->GPC['return']);
	exit();
}
// #### remove custom bid field ################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-bid-field')
{
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "bid_fields
		WHERE fieldid = '" . intval($ilance->GPC['id']) . "'
	");
}

if (!isset($ilance->GPC['subcmd']))
{
	$row_count = 0;
	$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
	while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
	{
		$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
		$language['language'] = $language['title'];
		$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$servicelanguages[] = $language;
		$row_count++;
	}
}
// requesting normal mode or edit/update mode?
$submit_field = $question = $question_description = $multiplechoicefield = $sort = $checked_active = $hiddenfield = $checked_active = '';
$fieldid = 0;
$field_inputtype_pulldown = '<select name="inputtype" style="font-family: verdana">';
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-bid-field')
{
	// multilanguage question and description
	$row_count = 0;
	$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
	while ($language = $ilance->db->fetch_array($languages))
	{
		$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
		$language['language'] = $language['title'];
		$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$sql = $ilance->db->query("
			SELECT question_$language[slng] AS question, description_$language[slng] AS question_description
			FROM " . DB_PREFIX . "bid_fields
			WHERE fieldid = '" . intval($ilance->GPC['id']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$language['question'] = $res['question'];	
				$language['question_description'] = $res['question_description'];	
			}
		}
		$servicelanguages[] = $language;
		$row_count++;
	}
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "bid_fields
		WHERE fieldid = '" . intval($ilance->GPC['id']) . "'
	");
	$res = $ilance->db->fetch_array($sql);
	if ($show['ADMINCP_TEST_MODE'])
	{
		$submit_field = '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" disabled="disabled" />';
	}
	else
	{
		$submit_field = '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" />';
	}
	$subcmd = 'updatebidfield';
	$hiddenfield = '<input type="hidden" value="' . $res['fieldid'] . '" name="fieldid" />';
	$question = $res['question_' . $_SESSION['ilancedata']['user']['slng']];
	$question_description = $res['description_' . $_SESSION['ilancedata']['user']['slng']];
	$multiplechoicefield = $res['multiplechoice'];
	$sort = $res['sort'];
	if ($res['visible'])
	{
		$checked_active = 'checked="checked"';
	}
	$field_inputtype_pulldown .= '<option value="yesno"'; if ($res['inputtype'] == "yesno") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . '{_radio_selection_box_yes_or_no_type_question}' . '</option>';
	$field_inputtype_pulldown .= '<option value="int"'; if ($res['inputtype'] == "int") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . '{_integer_field_numbers_only}' . '</option>';
	$field_inputtype_pulldown .= '<option value="textarea"'; if ($res['inputtype'] == "textarea") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . '{_textarea_field_multiline}' . '</option>';
	$field_inputtype_pulldown .= '<option value="text"'; if ($res['inputtype'] == "text") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . '{_input_text_field_singleline}' . '</option>';
	$field_inputtype_pulldown .= '<option value="multiplechoice"'; if ($res['inputtype'] == "multiplechoice") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . '{_multiple_choice_enter_values_below}' . '</option>';
	$field_inputtype_pulldown .= '<option value="pulldown"'; if ($res['inputtype'] == "pulldown") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . '{_pulldown_menu_enter_values_below}' . '</option>';
	$field_inputtype_pulldown .= '<option value="date"'; if ($res['inputtype'] == "date") { $field_inputtype_pulldown .= ' selected="selected"'; } $field_inputtype_pulldown .= '>' . '{_date_input_field}' . '</option>';
}
else
{
	$submit_field = ($show['ADMINCP_TEST_MODE'])
		? '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" disabled="disabled" />'
		: '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" />';
	$subcmd = 'addbidfield';
	$field_inputtype_pulldown .= '<option value="yesno">' . '{_radio_selection_box_yes_or_no_type_question}' . '</option>';
	$field_inputtype_pulldown .= '<option value="int">' . '{_integer_field_numbers_only}' . '</option>';
	$field_inputtype_pulldown .= '<option value="textarea">' . '{_textarea_field_multiline}' . '</option>';
	$field_inputtype_pulldown .= '<option value="text">' . '{_input_text_field_singleline}' . '</option>';
	$field_inputtype_pulldown .= '<option value="multiplechoice">' . '{_multiple_choice_enter_values_below}' . '</option>';
	$field_inputtype_pulldown .= '<option value="pulldown">' . '{_pulldown_menu_enter_values_below}' . '</option>';
	$field_inputtype_pulldown .= '<option value="date">' . '{_date_input_field}' . '</option>';
}
$field_inputtype_pulldown .= '</select>';
// #### select existing bid fields #############################
$no_bidfields = true;
$sql = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "bid_fields
	ORDER BY sort ASC
");
if ($ilance->db->num_rows($sql) > 0)
{
	$no_bidfields = false;
	$row_count = 0;
	$slng = $_SESSION['ilancedata']['user']['slng'];
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$res['description'] = $res['description_'.$slng];
		$res['question'] = $res['question_'.$slng];
		$res['sortinput'] = '<input type="text" name="sort[' . $res['fieldid'] . ']" value="' . $res['sort'] . '" class="input" size="3" style="text-align:center" />';
		$res['question_active'] = ($res['visible'])
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />'
			: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
		$res['edit'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_edit-bid-field&amp;id=' . $res['fieldid'] . '#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		$res['remove'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_remove-bid-field&amp;id=' . $res['fieldid'] . '" onclick="return confirm_js(\'' . '{_removing_this_bid_field_will_additionally_remove_all_associated}' . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$res['catcount'] = $ilance->bid_fields->fetch_categories_assigned($res['fieldid'], true);
		$res['answercount'] = $ilance->bid_fields->fetch_answer_count_submitted($res['fieldid']);
		$res['count'] = $ilance->language->construct_phrase('{_x_categories_using_this_bid_field_with_x_answers_from_bidders}', array($res['catcount'], $res['answercount']));
		$bidfields[] = $res;
		$row_count++;
	}
}
$acceptedgroupby = array('project_id', 'bidstatus');
if (!isset($ilance->GPC['subcmd']))
{
	$ilance->GPC['subcmd'] = '';
}
$orderby = 'DESC';
if (isset($ilance->GPC['orderby']))
{
	$orderby = $ilance->GPC['orderby'];
}
$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
$limit = 'ORDER BY b.bid_id ' . $orderby . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$addquery = $addquery2 = $addquery3 = $addquery4 = $project_id = $user_id = $bidstatus = $bid_id = '';
// #### searching by listing id number #################################
if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
{
	$project_id = intval($ilance->GPC['project_id']);
	$addquery = "AND p.project_id = '" . $project_id . "'";
}
// #### searching by user id number ####################################
if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
{
	$user_id = intval($ilance->GPC['user_id']);
	$addquery2 = "AND u.user_id = '" . $user_id . "'";
}
// #### searching by bid status ########################################
if (isset($ilance->GPC['bidstatus']) AND $ilance->GPC['bidstatus'] != '')
{
	$bidstatus = $ilance->GPC['bidstatus'];
	$addquery3 = "AND b.bidstatus = '" . $ilance->db->escape_string($bidstatus) . "'";
}
// #### searching by bid id number #####################################
if (isset($ilance->GPC['bid_id']) AND $ilance->GPC['bid_id'] > 0)
{
	$bid_id = intval($ilance->GPC['bid_id']);
	$addquery4 = "AND b.id = '" . $bid_id . "'";
}
$resultbids = $ilance->db->query("
	SELECT b.id, b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.bidamounttype, b.bidcustom, b.fvf, p.project_id, p.escrow_id, p.cid, p.description, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
	FROM " . DB_PREFIX . "project_realtimebids AS b,
	" . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "users AS u
	WHERE b.project_id = p.project_id
	    AND p.project_state = 'service'
	    AND u.user_id = b.user_id
	    $addquery
	    $addquery2
	    $addquery3
	    $addquery4
	    $limit
");
$numberrows = $ilance->db->query("
	SELECT b.id, b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.bidamounttype, b.bidcustom, b.fvf, p.project_id, p.escrow_id, p.cid, p.description, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
	FROM " . DB_PREFIX . "project_realtimebids AS b,
	" . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "users AS u
	WHERE b.project_id = p.project_id
	    AND p.project_state = 'service'
	    AND u.user_id = b.user_id
	    $addquery
	    $addquery2
	    $addquery3
	    $addquery4
");
$number = $ilance->db->num_rows($numberrows);
$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
if ($ilance->db->num_rows($resultbids) > 0)
{
	$show['no_servicebids'] = false;
	$row_count = 0;
	while ($bidrows = $ilance->db->fetch_array($resultbids, DB_ASSOC))
	{			
		if($ilance->categories->bidgrouping($bidrows['cid']) == 1)
		{
			$sql = $ilance->db->query("SELECT *
				FROM " . DB_PREFIX . "project_bids
				WHERE bid_id='".$bidrows['bid_id']."' 
					AND project_id='".$bidrows['project_id']."'
					AND project_user_id='".$bidrows['project_user_id']."'
					AND user_id='".$bidrows['user_id']."'
					AND date_added='".$bidrows['date_added']."'
					AND date_awarded='".$bidrows['date_awarded']."'
					AND date_updated='".$bidrows['date_updated']."'
			");
			if($ilance->db->num_rows($sql) == 0)
			{
				continue;
			}
		}
		$bidrows['bid_id'] = $bidrows['id'];
		$sql_user_results = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $bidrows['project_user_id'] . "'
		");
		$res_project_user = $ilance->db->fetch_array($sql_user_results, DB_ASSOC);
		$bidrows['fvf'] = ($bidrows['fvf'] > 0)
			? $ilance->currency->format($bidrows['fvf'])
			: '{_none}';
			
		$bidrows['bid_datetime'] = print_date($bidrows['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$bidrows['bidamount'] = $ilance->currency->format($bidrows['bidamount'], $bidrows['currencyid']);
		$bidrows['delivery'] = ($bidrows['estimate_days'] <= 1) ? $bidrows['estimate_days'] . ' ' . '{_day}' : $bidrows['estimate_days'] . ' ' . '{_days}';
		$bidrows['proposal'] = stripslashes($bidrows['proposal']);
		$bidrows['isonline'] = print_online_status($bidrows['user_id']);
		$bidrows['provider'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $bidrows['user_id'] . '">' . stripslashes($bidrows['username']) . '</a>';
		$bidrows['level'] = $ilance->subscription->print_subscription_icon($bidrows['user_id']);
		$bidrows['city'] = ucfirst($bidrows['city']);
		$bidrows['state'] = ucfirst($bidrows['state']);
		$bidrows['zip'] = trim(mb_strtoupper($bidrows['zip_code']));
		$bidrows['location'] = $bidrows['state'].' &gt; '.$ilance->common_location->print_user_country($bidrows['user_id'], fetch_site_slng());
		$bidrows['title'] = fetch_auction('project_title', $bidrows['project_id']);
		$sqlattachments = $ilance->db->query("
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
			FROM " . DB_PREFIX . "attachment
			WHERE attachtype = 'bid'
			    AND project_id = '" . $bidrows['project_id'] . "'
			    AND user_id = '" . $bidrows['user_id'] . "'
			    AND visible = '1'
		");
		if ($ilance->db->num_rows($sqlattachments) > 0)
		{
			$bidrows['bidattach'] = '';
			while ($resattach = $ilance->db->fetch_array($sqlattachments, DB_ASSOC))
			{
				$bidrows['bidattach'] .= '<tr>';
				$bidrows['bidattach'] .= '<td align="left" class="smaller"><strong>' . '{_attachments}' . '</strong><br />';
				$bidrows['bidattach'] .= '<span class="smaller" title="' . $resattach['filename'] . '" style="word-spacing:-6px"><font color="888888">';
				$tempvariable_underscore = str_replace("_", "_ ", $resattach['filename']);
				$tempvariable = str_replace("-", "- ", $tempvariable_underscore);
				$bidrows['bidattach'] .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="" id="" /><a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $resattach['filehash'] . '" target="_blank">' . $tempvariable . '</a></font></span><br />';
				$bidrows['bidattach'] .= '</td>';
				$bidrows['bidattach'] .= '</tr>';
			}
		}
		else
		{
			$bidrows['bidattach'] = '';
		}
		$bidrows['award'] = '[' . $bidrows['bidstatus'] . ']';
		$bidrows['delete'] = '<input type="checkbox" name="bidid[]" value="' . $bidrows['bid_id'] . '" />';
		$bidrows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$servicebids[] = $bidrows;
		$row_count++;
	}
}
else
{
	$show['no_servicebids'] = true;
}
if (!isset($ilance->GPC['project_id']))
{
	$ilance->GPC['project_id'] = 0;
}
if (!isset($ilance->GPC['user_id']))
{
	$ilance->GPC['user_id'] = 0;
}
if (!isset($ilance->GPC['orderby']))
{
	$ilance->GPC['orderby'] = '';
}
if (!isset($ilance->GPC['bidstatus']))
{
	$ilance->GPC['bidstatus'] = '';
}
if (!isset($ilance->GPC['groupby']))
{
	$ilance->GPC['groupby'] = '';
}
$scriptpage = $ilpage['distribution'] . '?cmd=bids&amp;subcmd=' . $ilance->GPC['subcmd'] . '&amp;project_id=' . $ilance->GPC['project_id'] . '&amp;user_id=' . $ilance->GPC['user_id'] . '&amp;bidstatus=' . $ilance->GPC['bidstatus'] . '&amp;orderby=' . $ilance->GPC['orderby'];
$serviceprevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);
$orderby = 'DESC';
if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']))
{
	$orderby = $ilance->GPC['orderby'];
}
$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
$limit2 = 'ORDER BY b.bid_id ' . $orderby . ' LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$addquery = $addquery2 = $addquery3 = $addquery4 = $bid_id = $bidstatus = $user_id = $project_id = "";
if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
{
	$project_id = intval($ilance->GPC['project_id']);
	$addquery = "AND p.project_id = '" . $project_id . "'";
}
if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
{
	$user_id = intval($ilance->GPC['user_id']);
	$addquery2 = "AND u.user_id = '" . $user_id . "'";
}
if (isset($ilance->GPC['bidstatus']) AND $ilance->GPC['bidstatus'] != '')
{
	$bidstatus = $ilance->GPC['bidstatus'];
	$addquery3 = "AND b.bidstatus = '" . $ilance->db->escape_string($bidstatus) . "'";
}
if (isset($ilance->GPC['bid_id']) AND $ilance->GPC['bid_id'] > 0)
{
	$bid_id = intval($ilance->GPC['bid_id']);
	$addquery4 = "AND b.bid_id = '" . $bid_id . "'";
}
$resultbids2 = $ilance->db->query("
	SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.bidamounttype, b.bidcustom, b.isproxybid, p.project_id, p.escrow_id, p.cid, p.description, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
	FROM " . DB_PREFIX . "project_bids AS b,
	" . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "users AS u
	WHERE b.project_id = p.project_id
	    AND p.project_state = 'product'
	    AND u.user_id = b.user_id
	    $addquery
	    $addquery2
	    $addquery3
	    $addquery4
	    $limit2
");
$numberrows2 = $ilance->db->query("
	SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.bidamounttype, b.bidcustom, b.isproxybid, p.project_id, p.escrow_id, p.cid, p.description, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
	FROM " . DB_PREFIX . "project_bids AS b,
	" . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "users AS u
	WHERE b.project_id = p.project_id
	    AND p.project_state = 'product'
	    AND u.user_id = b.user_id
	    $addquery
	    $addquery2
	    $addquery3
	    $addquery4
");
$number2 = $ilance->db->num_rows($numberrows2);
$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
if ($ilance->db->num_rows($resultbids2) > 0)
{
	$row_count = 0;
	while ($bidrows = $ilance->db->fetch_array($resultbids2, DB_ASSOC))
	{
		$bidrows['bid_datetime'] = print_date($bidrows['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$sql_user_results = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE user_id = '" . $bidrows['project_user_id'] . "'");
		$res_project_user = $ilance->db->fetch_array($sql_user_results);
		$rowbeforeexchange = $bidrows['bidamount'];
		$bidrows['bidamount'] = $ilance->currency->format($rowbeforeexchange, $bidrows['currencyid']);
		$bidrows['isonline'] = print_online_status($bidrows['user_id']);
		$bidrows['provider'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $bidrows['user_id'] . '">' . stripslashes($bidrows['username']) . '</a>';
		$bidrows['city'] = ucfirst($bidrows['city']);
		$bidrows['state'] = ucfirst($bidrows['state']);
		$bidrows['zip'] = trim(mb_strtoupper($bidrows['zip_code']));
		$bidrows['location'] = $bidrows['state'] . ' &gt; ' . $ilance->common_location->print_user_country($bidrows['user_id'], fetch_site_slng());
		$bidrows['title'] = fetch_auction('project_title', $bidrows['project_id']);
		if ($bidrows['bidstatus'] == 'awarded')
		{
			$bidrows['award'] = '[' . '{_winner_lower}' . ']';
		}
		else
		{
			$bidrows['award'] = '[' . $bidrows['bidstatus'] . ']';
		}
		
		if ($bidrows['isproxybid'])
		{
			$bidrows['proxy'] = '<span style="color:#ff6600">[' . '{_proxy_bid_lc}' . ']</span>';
		}
		else
		{
			$bidrows['proxy'] = '-';	
		}
		$bidrows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$bidrows['delete'] = '<input type="checkbox" name="bidid[]" value="' . $bidrows['bid_id'] . '" />';
		$productbids[] = $bidrows;
		$row_count++;
	}
}
else
{
	$show['no_productbids'] = true;
}
if (empty($ilance->GPC['project_id']))
{
	$ilance->GPC['project_id'] = 0;
}
if (empty($ilance->GPC['user_id']))
{
	$ilance->GPC['user_id'] = 0;
}
if (empty($ilance->GPC['bidstatus']))
{
	$ilance->GPC['bidstatus'] = '';
}
if (empty($ilance->GPC['orderby']))
{
	$ilance->GPC['orderby'] = 'DESC';
}
if (!isset($show['no_productbids']))
{
	$productprevnext = print_pagnation($number2, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], $counter2, $ilpage['distribution'] . '?cmd=bids&amp;subcmd=' . $ilance->GPC['subcmd'] . '&amp;project_id='.$ilance->GPC['project_id'].'&amp;user_id='.$ilance->GPC['user_id'].'&amp;bidstatus='.$ilance->GPC['bidstatus'].'&amp;orderby='.$ilance->GPC['orderby'], 'page2');
}
// #### BID INCREMENT GROUPS ###################################
$sql = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "increments_groups
");
if ($ilance->db->num_rows($sql) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		// fetch increment values in this group
		$sqlfees = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "increments
			WHERE groupname = '" . $row['groupname'] . "'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sqlfees) > 0)
		{
			$row_count2 = 0;
			while ($rows = $ilance->db->fetch_array($sqlfees, DB_ASSOC))
			{
				$rows['from'] = $ilance->currency->format($rows['increment_from']);
				$rows['to'] = ($rows['increment_to'] != '-1') ? $ilance->currency->format($rows['increment_to']) : '{_or_more}';
				$rows['amount'] = $ilance->currency->format($rows['amount']);
				$rows['actions'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_edit-increment&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['incrementid'] . '#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_remove-increment&amp;groupid='.$row['groupid'].'&amp;id='.$rows['incrementid'].'" onclick="return confirm_js(\'' . '{_please_take_a_moment_to_confirm_your_action}' . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$GLOBALS['increments' . $row['groupid']][] = $rows;
				$row_count2++;
			}
		}
		else
		{
			$GLOBALS['no_increments' . $row['groupid']][] = 1;	
		}
		$row['remove_group'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_remove-increment-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'' . '{_please_take_a_moment_to_confirm_your_action}' . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$row['edit'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_edit-increment-group&amp;groupid=' . $row['groupid'] . '#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		$row['groupcount'] = $ilance->admincp->fetch_increment_catcount($row['groupname']);
		$row['groupnameplain'] = $row['groupname'];
		$row['groupname'] = '<a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_edit-increment-group&amp;groupid=' . $row['groupid'] . '#editgroup">' . $row['groupname'] . '</a>';
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$increment_groups[] = $row;
		$increment_groups2[] = $row;
		$row_count++;
	}
	$show['no_increment_groups'] = false;
}
else
{
	$show['no_increment_groups'] = true;
}
// #### INSERT BID INCREMENT HANDLER ###########################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-increment' AND isset($ilance->GPC['increment_from']) AND isset($ilance->GPC['increment_to']) AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	$ilance->GPC['groupname'] = $ilance->db->fetch_field(DB_PREFIX . "increments_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
	$ilance->GPC['cid'] = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	
	$ilance->admincp->insert_bid_increment($ilance->GPC['increment_from'], $ilance->GPC['increment_to'], $ilance->GPC['amount'], $ilance->GPC['cid'], $ilance->GPC['sort'], $ilance->GPC['groupname']);
	
	print_action_success('{_new_product_bid_increment_range_was_successfully_added}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE CATEGORY INCREMENT ##############################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-increment' AND isset($ilance->GPC['increment_from']) AND isset($ilance->GPC['increment_to']) AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	$ilance->GPC['groupname'] = $ilance->db->fetch_field(DB_PREFIX . "increments_groups", "groupid = '".intval($ilance->GPC['groupid'])."'", "groupname");
	$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
	$ilance->admincp->update_bid_increment($ilance->GPC['id'], $ilance->GPC['increment_from'], $ilance->GPC['increment_to'], $ilance->GPC['amount'], 0, $ilance->GPC['sort'], $ilance->GPC['groupname']);
	
	print_action_success('{_bid_increment_range_was_successfully_updated}', $ilance->GPC['return']);
	exit();
}
// #### REMOVE CATEGORY INCREMENT ##############################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-increment' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
{
	$ilance->admincp->remove_bid_increment($ilance->GPC['id']);
	
	print_action_success('{_bid_increment_range_was_removed}', $ilpage['distribution'] . '?cmd=bids');
	exit();
}
// #### INSERT INCREMENT GROUP HANDLER #########################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-increment-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['description']))
{
	$ilance->admincp->insert_increment_group($ilance->GPC['groupname'], $ilance->GPC['description']);
	
	print_action_success('{_new_product_bid_increment_group_created}', $ilpage['distribution'] . '?cmd=bids');
	exit();
}
// #### UPDATE INCREMENT GROUP HANDLER #########################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-increment-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['description']))
{
	$ilance->admincp->update_increment_group($ilance->GPC['groupid'], $ilance->GPC['groupname'], $ilance->GPC['description']);
	
	print_action_success('{_bid_increment_group_was_updated}', $ilpage['distribution'] . '?cmd=bids');
	exit();
}
// #### REMOVE INCREMENT FEE GROUP HANDLER #####################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-increment-group' AND isset($ilance->GPC['groupid']))
{
	$ilance->admincp->remove_increment_group($ilance->GPC['groupid']);
	
	print_action_success('{_bid_increment_group_was_removed}', $ilpage['distribution'] . '?cmd=bids');
	exit();
}
// #### REMOVE INCREMENT HANDLER ###############################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-increment' AND isset($ilance->GPC['groupid']) AND isset($ilance->GPC['id']))
{
	$ilance->admincp->remove_bid_increment($ilance->GPC['id']);
	
	print_action_success('{_bid_increment_range_was_removed}', $ilpage['distribution'] . '?cmd=bids');
	exit();
}
// #### CALLED WHEN ADMIN CLICKS EDIT INCREMENT FEE GROUP PENCIL ICON #####
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-increment-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "increments_groups
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql);
	$incrementgroupname = $res['groupname'];
	$incrementgroupdescription = $res['description'];
	
	if ($show['ADMINCP_TEST_MODE'])
	{
		$submitincrement = '<input type="submit" value=" ' . '{_save}' . ' " style="font-size:15px" class="buttons" disabled="disabled" />';
	}
	else
	{
		$submitincrement = '<input type="submit" value=" ' . '{_save}' . ' " style="font-size:15px" class="buttons" />';
	}
	$subcmdincrementgroup = '_update-increment-group';
	$hiddenincrementgroupid2 = '<input type="hidden" name="groupid" value="' . $res['groupid'] . '" />';
}
else 
{
	$incrementgroupname = $incrementgroupdescription = $hiddenincrementgroupid2 = '';
	if ($show['ADMINCP_TEST_MODE'])
	{
		$submitincrement = '<input type="submit" value=" ' . '{_save}' . ' " style="font-size:15px" class="buttons" disabled="disabled" />';
	}
	else
	{
		$submitincrement = '<input type="submit" value=" ' . '{_save}' . ' " style="font-size:15px" class="buttons" />';
	}
	$subcmdincrementgroup = 'insert-increment-group';
}
// does admin edit specific increment?
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-increment' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	$incrementgrouppulldown = $ilance->admincp->print_increment_group_pulldown(intval($ilance->GPC['groupid']), 1, 'product');
	$sqlincrements = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "increments
		WHERE incrementid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sqlincrements) > 0)
	{
		while ($rows = $ilance->db->fetch_array($sqlincrements))
		{	
			$incfrom = $rows['increment_from'];
			$incto = $rows['increment_to'];
			$incamount = $rows['amount'];
			$incsort = $rows['sort'];
			if ($show['ADMINCP_TEST_MODE'])
			{
				$incsubmit = '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" disabled="disabled" />';
			}
			else
			{
				$incsubmit = '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" />';
			}
			$incform = 'update-increment';
			$inchidden = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
		}
	}
}
else 
{
	$incrementgrouppulldown = $ilance->admincp->print_increment_group_pulldown('', 1, 'product');
	$incfrom = $incto = $incamount = '0.00';
	$incsort = '10';
	if ($show['ADMINCP_TEST_MODE'])
	{
		$incsubmit = '<input type="submit" value=" ' . '{_save}' . ' " style="font-size:15px" class="buttons" disabled="disabled" />';
	}
	else
	{
		$incsubmit = '<input type="submit" value=" ' . '{_save}' . ' " style="font-size:15px" class="buttons" />';
	}
	$incform = 'insert-increment';
	$inchidden = '';
}
// #### bid configuration ##############################################
$configuration_productbid = $ilance->admincp->construct_admin_input('productbid', $ilpage['distribution'] . '?cmd=bids');
$pprint_array = array('configuration_productbid','bid_id','user_id','project_id','incrementgrouppulldown','incsort','inchidden','incform','incsubmit','incamount','incfrom','incto','hiddenincrementgroupid2','incrementgroupname','incrementgroupdescription','submitincrement','subcmdincrementgroup','checked_active','submit_field','hiddenfield','multiplechoicefield','sort','question','question_description','subcmd','field_inputtype_pulldown','projectid','serviceprevnext','productprevnext','id');

($apihook = $ilance->api('admincp_bids_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'bids.html', 1);
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'servicebids');
$ilance->template->parse_loop('main', 'productbids');
$ilance->template->parse_loop('main', 'bidfields');
$ilance->template->parse_loop('main', 'servicelanguages');
$ilance->template->parse_loop('main', 'increment_groups2');
$ilance->template->parse_loop('main', 'increment_groups');
if (!isset($increment_groups))
{
	$increment_groups = array();
}
@reset($increment_groups);
while ($i = @each($increment_groups))
{
	$ilance->template->parse_loop('main', 'increments' . $i['value']['groupid']);
}
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>