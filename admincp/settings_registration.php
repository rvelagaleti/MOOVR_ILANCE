<?php

if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=registration', $_SESSION['ilancedata']['user']['slng']);
$area_title = '{_registration_question_management}';
$page_title = SITE_NAME . ' - {_registration_question_management}';
$configuration_registrationdisplay = $ilance->admincp->construct_admin_input('registrationdisplay', $ilpage['settings'] . '?cmd=registration');
$configuration_registrationupsell = $ilance->admincp->construct_admin_input('registrationupsell', $ilpage['settings'] . '?cmd=registration');
$ilance->GPC['id'] = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
// #### remove registration question ###########################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-register-question')
{
	$ilance->admincp->remove_registration_question(intval($ilance->GPC['id']));
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=registration');
	exit();
}
// #### report category question #############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_report-register-questions-sort')
{
	$sql = "SELECT  a.answer AS answer, a.date AS date, u.username AS username,c.question_" . $_SESSION['ilancedata']['user']['slng'] . " AS question
		FROM " . DB_PREFIX . "register_answers AS a," . DB_PREFIX . "users AS u," . DB_PREFIX . "register_questions AS c
		WHERE a.questionid = '" . intval($ilance->GPC['reports_qid']) . "' AND a.user_id = u.user_id AND c.questionid = a.questionid ORDER BY a.user_id DESC
	";
	$fields = array (
	    array ('username', '{_username}'),
	    array ('question', '{_questions}'),
	    array ('date', '{_date}'),
	    array ('answer', '{_answer}'),
	);
	$searchquery = '';
	foreach ($fields AS $column)
	{
		$fieldsToGenerate[] = $column[0];
		$headings[] = $column[1];
	}
	$action = $ilance->GPC['action'];
	$ilance->GPC['doshow'] = '';
	$data = $ilance->admincp->fetch_reporting_fields($sql, $fieldsToGenerate);
	switch ($action)
	{
		case 'csv':
			{
				$reportoutput = $ilance->admincp->construct_csv_data_register($data, $headings);
				break;
			}
		case 'tsv':
			{
				$reportoutput = $ilance->admincp->construct_tsv_data_register($data, $headings);
				break;
			}
		case 'list':
		default:
			{
				$reportoutput = $result = '';
				$questionsql = $ilance->db->query("
					SELECT  multiplechoice, inputtype, question_" . $_SESSION['ilancedata']['user']['slng'] . " AS question, inputtype
					FROM " . DB_PREFIX . "register_questions
					WHERE questionid = '" . intval($ilance->GPC['reports_qid']) . "' AND (inputtype = 'yesno' OR inputtype = 'pulldown' OR inputtype='multiplechoice')
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($questionsql) > 0)
				{
					$fetch_question = $ilance->db->fetch_array($questionsql, DB_ASSOC);
					if ($fetch_question['inputtype'] == 'yesno')
					{
						$answeryessql = $ilance->db->query("
							SELECT answer
							FROM " . DB_PREFIX . "register_answers
							WHERE questionid = '" . intval($ilance->GPC['reports_qid']) . "'
								AND answer = '1'
						", 0, null, __FILE__, __LINE__);
						$count_yes = $ilance->db->num_rows($answeryessql);
						$answernosql = $ilance->db->query("
							SELECT answer
							FROM " . DB_PREFIX . "register_answers
							WHERE questionid = '" . intval($ilance->GPC['reports_qid']) . "'
								AND answer = '0'
						", 0, null, __FILE__, __LINE__);
						$count_no = $ilance->db->num_rows($answernosql);
						$reportoutput = '<div class="tab-row">
<h2 class="tab selected" id="0">{_question}</h2>
<div style="padding-top:12px;padding-bottom:12px;">
<div class="tab-page" style="display: block; -webkit-box-shadow: #888 0px 3px 10px;">
<table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '">
<tr class="alt3"> <td colspan="2">' . $fetch_question['question'] . '</td></tr><tr class="alt1"><td>{_yes} | ' . $count_yes . '</td><td>{_no} | ' . $count_no . '</td></tr></table></div></div></div>';
					}
					if ($fetch_question['inputtype'] == 'pulldown' OR $fetch_question['inputtype'] == 'multiplechoice')
					{
						$explode_answer = explode('|', $fetch_question['multiplechoice']);
						$answersql = $ilance->db->query("
							SELECT  answer
							FROM " . DB_PREFIX . "register_answers
							WHERE questionid = '" . intval($ilance->GPC['reports_qid']) . "'
						", 0, null, __FILE__, __LINE__);
						while ($fetchanswer = $ilance->db->fetch_array($answersql, DB_ASSOC))
						{
							if (is_serialized($fetchanswer['answer']))
							{
								$answer = unserialize($fetchanswer['answer']);
								foreach ($explode_answer AS $value)
								{
									if (in_array($value, $answer))
									{
										$val[$value] = (isset($val[$value])) ? $val[$value] + 1 : 1;
									}
								}
							}
						}
						foreach ($explode_answer AS $value)
						{
							$text_value = (isset($val[$value])) ? $val[$value] : 0;
							$result .= '<tr class="alt1"><td colspan="2">' . $value . ' | ' . $text_value . '</td></tr>';
						}
					}
					$reportoutput = '<div class="tab-row">
<h2 class="tab selected" id="0">{_question}</h2>
<div style="padding-top:12px;padding-bottom:12px;">
<div class="tab-page" style="display: block; -webkit-box-shadow: #888 0px 3px 10px;">
<table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '">
<tr class="alt3"> <td colspan="2">' . $fetch_question['question'] . '</td></tr>' . $result . '</table></div></div></div>';
				}
				$reportoutput .= $ilance->admincp->construct_html_table_register($data, $headings);
				break;
			}
	}
	$ilance->template->templateregistry['reportoutput'] = $reportoutput;
	$reportoutput = $ilance->template->parse_template_phrases('reportoutput');
	$report_output = $reportoutput;
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "register-$timeStamp";
	if ($action == 'csv')
	{
		header("Pragma: cache");
		header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
		header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
		echo $reportoutput;
		die();
	}
	else if ($action == 'tsv')
	{
		header("Pragma: cache");
		header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
		header("Content-Disposition: attachment; filename=" . $fileName . ".txt");
		echo $reportoutput;
		die();
	}
}
// #### UPDATE QUESTIONS SORT FOR A PROFILE GROUP OF QUESTIONS
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-register-questions-sort')
{
	foreach ($ilance->GPC['sort'] AS $key => $value)
	{
		if (!empty($key) AND !empty($value))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "register_questions
				SET sort = '" . intval($value) . "'
				WHERE questionid = '" . intval($key) . "'
				LIMIT 1
			");
		}
	}
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### update registration question ###########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-register-question' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : 0;
	$required = isset($ilance->GPC['required']) ? intval($ilance->GPC['required']) : 0;
	$profile = isset($ilance->GPC['public']) ? intval($ilance->GPC['public']) : 0;
	$guests = isset($ilance->GPC['guests']) ? intval($ilance->GPC['guests']) : 0;
	$displayvalues = isset($ilance->GPC['multiplechoice']) ? $ilance->GPC['multiplechoice'] : '';
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
	$cansearch = isset($ilance->GPC['cansearch']) ? intval($ilance->GPC['cansearch']) : 0;
	$formdefault = isset($ilance->GPC['formdefault']) ? $ilance->GPC['formdefault'] : '';
	$roleid = $query1 = $query2 = '';
	if (isset($ilance->GPC['roleid']))
	{
		if (is_array($ilance->GPC['roleid']))
		{
			foreach ($ilance->GPC['roleid'] AS $key => $value)
			{
				$roleid .=!empty($roleid) ? '|' . $value : $value;
			}
		}
		else
		{
			$roleid = $ilance->GPC['roleid'];
		}
	}
	if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['question'] AS $slng => $value)
		{
			$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	$ilance->db->query("
			UPDATE " . DB_PREFIX . "register_questions
			SET pageid = '" . intval($ilance->GPC['pageid']) . "',
			$query1
			$query2
			inputtype = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			formname = '" . $ilance->db->escape_string($ilance->GPC['formname']) . "',
			formdefault = '" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
			sort = '" . intval($ilance->GPC['sort']) . "',
			visible = '" . $visible . "',
			required = '" . $required . "',
			profile = '" . $profile . "',
			multiplechoice = '" . $displayvalues . "',
			cansearch = '" . $cansearch . "',
			guests = '" . $guests . "',
			roleid = '" . $roleid . "'
			WHERE questionid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### add new registration question ##########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-register-question' AND isset($ilance->GPC['pageid']) AND $ilance->GPC['pageid'] > 0 AND !empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']) AND isset($ilance->GPC['formname']) AND isset($ilance->GPC['inputtype']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : 0;
	$required = isset($ilance->GPC['required']) ? intval($ilance->GPC['required']) : 0;
	$profile = isset($ilance->GPC['public']) ? intval($ilance->GPC['public']) : 0;
	$guests = isset($ilance->GPC['guests']) ? intval($ilance->GPC['guests']) : 0;
	$displayvalues = isset($ilance->GPC['multiplechoice']) ? $ilance->GPC['multiplechoice'] : '';
	$cansearch = isset($ilance->GPC['cansearch']) ? intval($ilance->GPC['cansearch']) : 0;
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
	$formdefault = isset($ilance->GPC['formdefault']) ? $ilance->GPC['formdefault'] : '';
	$roleid = '';
	if (isset($ilance->GPC['roleid']))
	{
		if (is_array($ilance->GPC['roleid']))
		{
			foreach ($ilance->GPC['roleid'] AS $key => $value)
			{
				$roleid .=!empty($roleid) ? '|' . $value : $value;
			}
		}
		else
		{
			$roleid = $ilance->GPC['roleid'];
		}
	}
	$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "register_questions
			(questionid, pageid, formname, formdefault, inputtype, multiplechoice, sort, required, profile, cansearch, guests, roleid)
			VALUES(
			NULL,
			'" . intval($ilance->GPC['pageid']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['formname']) . "',
			'" . $formdefault . "',
			'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			'" . $displayvalues . "',
			'" . $sort . "',
			'" . $required . "',
			'" . $profile . "',
			'" . $cansearch . "',
			'" . $guests . "',
			'" . $roleid . "')
		");
	$insid = $ilance->db->insert_id();
	$query1 = $query2 = '';
	if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['question'] as $slng => $value)
		{
			$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
		foreach ($ilance->GPC['description'] as $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	$ilance->db->query("
			UPDATE " . DB_PREFIX . "register_questions
			SET
			$query1
			$query2
			visible = '" . $visible . "'
			WHERE questionid = '" . $insid . "'
			LIMIT 1
		");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### edit registration question edit handler ################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-register-question' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$sqlregq = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "register_questions
		WHERE questionid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	$resregq = $ilance->db->fetch_array($sqlregq);
	$register_page_pulldown = '<select name="pageid" class="select">
<option value="1"';
	($resregq['pageid'] == 1) ? $register_page_pulldown .= ' selected="selected"' : '';
	$register_page_pulldown .= '>{_page_1_member_details}</option>
<option value="2"';
	($resregq['pageid'] == 2) ? $register_page_pulldown .= ' selected="selected"' : '';
	$register_page_pulldown .= '>{_page_2_personal_details}</option>
<option value="3"';
	($resregq['pageid'] == 3) ? $register_page_pulldown .= ' selected="selected"' : '';
	$register_page_pulldown .= '>{_page_3_subscription_details}</option>
</select>';
	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
	$role_option = '';
	$sql_role = $ilance->db->query("SELECT r.roleid, r.purpose_$slng as purpose, r.title_$slng as title, r.custom, r.roletype, r.roleusertype, r.active
	                FROM " . DB_PREFIX . "subscription_roles r,
	                " . DB_PREFIX . "subscription s
	                WHERE r.roleid = s.roleid
	                	AND r.active = '1'
	                	AND s.active = 'yes'
	                	AND s.visible_registration = '1'
	                GROUP BY r.roleid ASC
		");
	while ($res_role = $ilance->db->fetch_array($sql_role, DB_ASSOC))
	{
		$checked = '';
		$roles = explode('|', $resregq['roleid']);
		if (is_array($roles))
		{
			foreach ($roles AS $key => $value)
			{
				if ($value == $res_role['roleid'])
				{
					$checked = 'checked="checked"';
				}
			}
		}
		$role_option .= '<input type="checkbox" id="' . $res_role['roleid'] . '" name="roleid[]" value="' . $res_role['roleid'] . '" ' . $checked . ' /> ' . stripslashes($res_role['title']) . ' - ' . stripslashes($res_role['purpose']) . '<br />';
	}
	$regquestion_subcmd = 'update-register-question';
	$regquestion_id_hidden = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
	$regsort = $resregq['sort'];
	$regchecked_visible = ($resregq['visible'] > 0) ? 'checked="checked"' : '';
	$regchecked_required = ($resregq['required'] > 0) ? 'checked="checked"' : '';
	$regchecked_public = ($resregq['profile'] > 0) ? 'checked="checked"' : '';
	$regchecked_guests = ($resregq['guests'] > 0) ? 'checked="checked"' : '';
	$regformname = $resregq['formname'];
	$regformdefault = $resregq['formdefault'];
	$regsubmit_profile_question = '<input type="submit" value=" {_save} " class="buttons" style="font-size:15px" /> &nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=registration">{_cancel}</a></span>';
}
else
{
	$register_page_pulldown = '<select name="pageid" class="select-250">
<option value="1">{_page_1_member_details}</option>
<option value="2">{_page_2_personal_details}</option>
<option value="3">{_page_3_subscription_details}</option>
</select>';
	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
	$role_option = '';
	$sql_role = $ilance->db->query("
			SELECT r.roleid, r.purpose_$slng as purpose, r.title_$slng as title, r.custom, r.roletype, r.roleusertype, r.active
	                FROM " . DB_PREFIX . "subscription_roles r,
	                " . DB_PREFIX . "subscription s
	                WHERE r.roleid = s.roleid
	                	AND r.active = '1'
	                	AND s.active = 'yes'
	                	AND s.visible_registration = '1'
	                GROUP BY r.roleid ASC
		");
	while ($res_role = $ilance->db->fetch_array($sql_role, DB_ASSOC))
	{
		$role_option .= '<input type="checkbox" id="' . $res_role['roleid'] . '" name="roleid[]" value="' . $res_role['roleid'] . '" checked="checked" /> ' . stripslashes($res_role['title']) . ' - ' . stripslashes($res_role['purpose']) . '<br />';
	}
	$regquestion_subcmd = 'insert-register-question';
	$regquestion_id_hidden = $regquestion = $regquestion_description = $regsort = '';
	$regchecked_visible = $regchecked_required = $regchecked_public = $regchecked_guests = $regformdefault = '';
	$regformname = construct_form_name(14);
	$regsubmit_profile_question = '<input type="submit" value=" {_save} " class="buttons" style="font-size:15px" />';
}
$regprofile_inputtype_pulldown = '<select name="inputtype" class="select-250">';
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-register-question')
{
	$regprofile_inputtype_pulldown .= '<option value="yesno"';
	if ($resregq['inputtype'] == "yesno")
	{
		$regprofile_inputtype_pulldown .= ' selected="selected"';
	} $regprofile_inputtype_pulldown .= '>{_radio_selection_box_yes_or_no_type_question}</option>';
	$regprofile_inputtype_pulldown .= '<option value="int"';
	if ($resregq['inputtype'] == "int")
	{
		$regprofile_inputtype_pulldown .= ' selected="selected"';
	} $regprofile_inputtype_pulldown .= '>{_integer_field_numbers_only}</option>';
	$regprofile_inputtype_pulldown .= '<option value="textarea"';
	if ($resregq['inputtype'] == "textarea")
	{
		$regprofile_inputtype_pulldown .= ' selected="selected"';
	} $regprofile_inputtype_pulldown .= '>{_textarea_field_multiline}</option>';
	$regprofile_inputtype_pulldown .= '<option value="text"';
	if ($resregq['inputtype'] == "text")
	{
		$regprofile_inputtype_pulldown .= ' selected="selected"';
	} $regprofile_inputtype_pulldown .= '>{_input_text_field_singleline}</option>';
	$regprofile_inputtype_pulldown .= '<option value="multiplechoice"';
	if ($resregq['inputtype'] == "multiplechoice")
	{
		$regprofile_inputtype_pulldown .= ' selected="selected"';
	} $regprofile_inputtype_pulldown .= '>{_multiple_choice_enter_values_below}</option>';
	$regprofile_inputtype_pulldown .= '<option value="pulldown"';
	if ($resregq['inputtype'] == "pulldown")
	{
		$regprofile_inputtype_pulldown .= ' selected="selected"';
	} $regprofile_inputtype_pulldown .= '>{_pulldown_menu_enter_values_below}</option>';
	$multiplechoice = $resregq['multiplechoice'];
	$checked_question_cansearch = ($resregq['cansearch']) ? 'checked="checked"' : '';
}
else
{
	$regprofile_inputtype_pulldown .= '<option value="yesno">{_radio_selection_box_yes_or_no_type_question}</option>';
	$regprofile_inputtype_pulldown .= '<option value="int">{_integer_field_numbers_only}</option>';
	$regprofile_inputtype_pulldown .= '<option value="textarea">{_textarea_field_multiline}</option>';
	$regprofile_inputtype_pulldown .= '<option value="text">{_input_text_field_singleline}</option>';
	$regprofile_inputtype_pulldown .= '<option value="multiplechoice">{_multiple_choice_enter_values_below}</option>';
	$regprofile_inputtype_pulldown .= '<option value="pulldown">{_pulldown_menu_enter_values_below}</option>';
	$multiplechoice = $checked_question_cansearch = '';
}
$regprofile_inputtype_pulldown .= '</select>';
$row_count = 0;
$registerlanguages = array ();
$languages = $ilance->db->query("
		SELECT languagecode, title
		FROM " . DB_PREFIX . "language
	");
while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
{
	$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
	$language['language'] = stripslashes($language['title']);
	$language['question'] = '';
	$language['description'] = '';
	$sql = $ilance->db->query("
			SELECT question_$language[slng] AS question, description_$language[slng] AS description
			FROM " . DB_PREFIX . "register_questions
			WHERE questionid = '" . intval($ilance->GPC['id']) . "'
		");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$language['question'] = stripslashes($res['question']);
			$language['description'] = stripslashes($res['description']);
			$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		}
	}
	$row_count++;
	$registerlanguages[] = $language;
}
$show['no_register_questions'] = true;
$regquestions = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "register_questions
		ORDER BY sort ASC
	");
$regquestions1 = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "register_questions
		ORDER BY sort ASC
	");
// #### reporting action #######################################
$reportaction = '<select name="action" class="select-250"><option value="list"';
if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'list')
{
	$reportaction .= ' selected="selected"';
}
$reportaction .= '>{_show_report_listings}</option>';
$reportaction .= '<option value="csv"';
if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'csv')
{
	$reportaction .= ' selected="selected"';
}
$reportaction .= '>{_download_comma_delimited_file}</option>';
$reportaction .= '<option value="tsv"';
if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'tsv')
{
	$reportaction .= ' selected="selected"';
}
$reportaction .= '>{_download_tab_delimited_file}</option></select>';
$reports_dropdown = '';
$show['register_questions'] = false;
if ($ilance->db->num_rows($regquestions1) > 0)
{
	$show['register_questions'] = true;
	$reports_dropdown = '<select name="reports_qid" class="select-250">';
	while ($rows = $ilance->db->fetch_array($regquestions1, DB_ASSOC))
	{
		$reports_dropdown .= '<option value="' . $rows['questionid'] . '">' . stripslashes($rows['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</option>';
	}
	$reports_dropdown .= '<select>';
}
if ($ilance->db->num_rows($regquestions) > 0)
{
	$row_count2 = 0;
	$show['no_register_questions'] = false;
	while ($rows = $ilance->db->fetch_array($regquestions, DB_ASSOC))
	{
		$rows['question'] = stripslashes($rows['question_' . $_SESSION['ilancedata']['user']['slng']]);
		$rows['question_description'] = stripslashes($rows['description_' . $_SESSION['ilancedata']['user']['slng']]);
		$rows['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=registration&amp;subcmd=_edit-register-question&amp;id=' . $rows['questionid'] . '#registrationquestion"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		$rows['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=registration&amp;subcmd=_remove-register-question&amp;id=' . $rows['questionid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$rows['question_active'] = ($rows['visible']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
		$rows['isrequired'] = ($rows['required']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
		$rows['inputtype'] = mb_strtolower($rows['inputtype']);
		$rows['sortinput'] = '<input type="text" name="sort[' . $rows['questionid'] . ']" value="' . $rows['sort'] . '" class="input" size="3" style="text-align:center" />';
		$rows['visibleprofile'] = ($rows['profile']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
		$rows['guestsprofile'] = ($rows['guests']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
		$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
		$register_questions[] = $rows;
		$row_count2++;
	}
}
$report_output = (isset($report_output)) ? $report_output : '';
$pprint_array = array ('role_option', 'checked_question_cansearch', 'report_output', 'multiplechoice', 'reportaction', 'regprofile_inputtype_pulldown', 'regsubmit_profile_question', 'regformdefault', 'regformname', 'regchecked_guests', 'regchecked_public', 'regchecked_required', 'regchecked_visible', 'regsort', 'regquestion_id_hidden', 'regquestion_subcmd', 'register_page_pulldown', 'configuration_registrationdisplay', 'configuration_registrationupsell', 'prevnext', 'reportrange', 'titlesinput', 'roletypepulldown', 'roleusertypepulldown', 'role_pulldown', 'migrate_billing_pulldown', 'migrate_plan_pulldown', 'commission_group_pulldown', 'permission_group_pulldown', 'currency', 'new_resource_item', 'reports_dropdown');

($apihook = $ilance->api('admincp_registration_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'registration.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'register_questions');
$ilance->template->parse_loop('main', 'registerlanguages');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();
?>