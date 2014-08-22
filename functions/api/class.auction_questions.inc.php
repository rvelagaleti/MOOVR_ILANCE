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
* Class to handle the auction posting interface for any type of auction supported in ILance.
*
* @package      iLance\Auction\Questions
* @version	4.0.0.8059
* @author       ILance
*/
class auction_questions extends auction
{
	/**
	* Function to handle all answerable auction questions within the posting system.
	*
	* @param       integer       category id
	* @param       integer       listing id
	* @param       string        display mode (input, preview, update, output, outputmini, api)
	* @param       string        category type (service or product)
	* @param       integer       number of columns
	* @param       boolean       display category finder table
	* @param       integer       answer output limit (default 10) 
	*
	* @return      string        HTML representation of the custom listing questions
	*/
	function construct_auction_questions($cid = 0, $projectid = 0, $mode = '', $type = '', $columns = 3, $categoryfindertable = false, $outputlimit = 10)
	{
		global $ilance, $ilpage, $phrase, $headinclude, $ilconfig, $show;
		$table1 = ($type == 'service') ? 'project_questions' : 'product_questions';
		$table2 = ($type == 'service') ? 'project_answers' : 'product_answers';
		$table3 = ($type == 'service') ? 'project_questions_choices' : 'product_questions_choices';
		$field1 = 'questionid, cid, question_' . $_SESSION['ilancedata']['user']['slng'] . ', description_' . $_SESSION['ilancedata']['user']['slng'] . ', formname, formdefault, inputtype, sort, visible, required, cansearch, canremove, recursive, guests';
		$categoryfindertable = true;
		$html = '';
		$cols = 0;
		$pid = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "parentid");
		$extracids = "AND (cid = '" . intval($cid) . "' OR cid = '-1')";
		$var = $ilance->categories->fetch_parent_ids($cid);
		$explode = explode(',', $var);
		if (in_array($pid, $explode))
		{
			$extracids = "AND (FIND_IN_SET(cid, '$var') OR cid = '-1')";
		}
		unset($explode, $var);
		// #### QUESTION DISPLAY TYPE ##################################
		// #### input mode #############################################
		if ($mode == 'input')
		{
			$sql = $ilance->db->query("
				SELECT $field1
				FROM " . DB_PREFIX . $table1 . "
				WHERE visible = '1'
					$extracids
				ORDER BY sort
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$show['categoryfindertable'] = true;
				$headinclude .= "
<script type=\"text/javascript\">
function customImage(imagename, imageurl, errors)
{
        document[imagename].src = imageurl;
        if (!haveerrors && errors)
        {
                haveerrors = errors;
                alert_js(phrase['_please_fix_the_fields_marked_with_a_warning_icon_and_retry_your_action']);
        }
}
function validatecustomform()
{
        haveerrors = 0;
";
				$isrequiredjs = '';
				$num = 0;
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
					{
						$formdefault = '';
						if (isset($res['formdefault']) AND $res['formdefault'] != '')
						{
							$formdefault = handle_input_keywords($res['formdefault']);
						}
						$overridejs = 0;
						$desc = stripslashes(handle_input_keywords($res['description_' . $_SESSION['ilancedata']['user']['slng']]));
						switch ($res['inputtype'])
						{
							case 'yesno':
							{
								$input = '<label for="' . handle_input_keywords($res['formname']) . '1" title="' . $desc . '"><input type="radio" id="' . handle_input_keywords($res['formname']) . '1" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="1" title="' . $desc . '" checked="checked"> {_yes} </label> <label for="' . handle_input_keywords($res['formname']) . '0" title="' . $desc . '"><input type="radio" id="' . handle_input_keywords($res['formname']) . '0" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" title="' . $desc . '" value="0"> {_no}</label>';
								$overridejs = 1;
								break;
							}
							case 'int':
							{
								$input = '<input class="input" size="3" type="number" id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" title="' . $desc . '" value="' . $formdefault . '" />';
								break;
							}
							case 'textarea':
							{
								$input = '<textarea id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" title="' . $desc . '" style="width:200px; height:44px; padding:2px" wrap="physical">' . $formdefault . '</textarea>';
								$input .= ($categoryfindertable == false) ? '<br /><div style="width:250px;" class="smaller"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)"> {_increase_size}</a>&nbsp;&nbsp;<span class="smaller">|</span>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)"> {_decrease_size}</a></div>' : '';
								break;
							}
							case 'text':
							{
								$input = '<input class="input" type="text" id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" title="' . $desc . '" value="' . $formdefault . '" />';
								break;
							}
							case 'url':
							{
								$input = '<input class="input" type="text" id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" title="' . $desc . '" value="' . $formdefault . '" />';
								break;
							}
							case 'multiplechoice':
							{
								$formdefault = $ilance->db->fetch_field(DB_PREFIX . $table1, "questionid = '" . intval($res['questionid']) . "'", "formdefault");
								$formdefault_array = explode('|', $formdefault);
								$sql2 = $ilance->db->query("
									SELECT choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
									FROM " . DB_PREFIX . $table3 . "
									WHERE questionid = '" . $res['questionid'] . "'
										AND visible = '1'
									ORDER BY sort
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql) > 0)
								{
									$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}<br /><select style="width:250px; height:70px" multiple name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . '][]" id="' . handle_input_keywords($res['formname']) . '" title="' . $desc . '" >';
									$input .= '<option value="">-</option><optgroup label="{_select}:">';
									while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
									{
										if ($res2['choice'] != '')
										{
											$isdefault = in_array($res2['choice'], $formdefault_array) ? 'selected="selected"' : "";
											$input .= '<option value="' . handle_input_keywords(trim($res2['choice'])) . '" ' . $isdefault . ' >' . handle_input_keywords(trim($res2['choice'])) . '</option>';
										}
									}
									$input .= '</optgroup></select>';
								}
								break;
							}
							case 'pulldown':
							{
								$formdefault = $ilance->db->fetch_field(DB_PREFIX . $table1, "questionid = '" . intval($res['questionid']) . "'", "formdefault");
								$sql2 = $ilance->db->query("
									SELECT choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
									FROM " . DB_PREFIX . $table3 . "
									WHERE questionid = '" . $res['questionid'] . "'
										AND visible = '1'
									ORDER BY sort
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql) > 0)
								{
									$input = '<select class="select" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . '][]" id="' . handle_input_keywords($res['formname']) . '" title="' . $desc . '" ><option value="">-</option>';
									while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
									{
										if ($res2['choice'] != '')
										{
											$isdefault = ($res2['choice'] == $formdefault) ? 'selected="selected"' : "";
											$input .= '<option value="' . handle_input_keywords(trim($res2['choice'])) . '" ' . $isdefault . '>' . handle_input_keywords(trim($res2['choice'])) . '</option>';
										}
									}
									$input .= '</select>';
								}
								break;
							}
						}
						$isrequired = '';
						if ($res['required'] AND $overridejs == 0)
						{
							$questionid = $res['questionid'];
							$formname = $res['formname'];
							if (isset($_POST['custom'][$questionid][$formname]))
							{
								if ((is_array($_POST['custom'][$questionid][$formname]) AND empty($_POST['custom'][$questionid][$formname]['0']) AND $_POST['custom'][$questionid][$formname]['0'] != '0') OR (empty($_POST['custom'][$questionid][$formname]) AND $_POST['custom'][$questionid][$formname] != '0'))
								{
									$isrequired = '<img name="custom[' . $res["questionid"] . '][' . handle_input_keywords($res["formname"]) . ']error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif" width="21" height="13" border="0" alt="{_this_form_field_is_required}" />';
								}
								else
								{
									$isrequired = '<img name="custom[' . $res["questionid"] . '][' . handle_input_keywords($res["formname"]) . ']error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="{_this_form_field_is_required}" />';
								}
							}
							else
							{
								$isrequired = '<img name="' . handle_input_keywords($res["formname"]) . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="{_this_form_field_is_required}" />';
								$headinclude .= "\n(fetch_js_object('" . stripslashes($res['formname']) . "').value.length < 1) ? customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
							}
						}
						if ($cols == 0)
						{
							$html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';
						}
						$html .= '<td width="25%" valign="top"><div>' . (($res['required']) ? '<span class="red" title="{_required}">*</span> ' : '') . '<strong>' . stripslashes(handle_input_keywords($res['question_' . $_SESSION['ilancedata']['user']['slng']])) . '</strong> ' . (!empty($desc) ? '<a href="javascript:void(0)" onmouseover="Tip(\'<div>' . addslashes($desc) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>' : '') . '<div style="padding-top:3px">' . $input . ' ' . $isrequired . '</div></div><div style="padding-bottom:12px"></div></td>';
						$cols++;
						if ($cols == $columns)
						{
							$html .= '</tr>';
							$cols = 0;
						}
					}
				}
				if ($cols != $columns AND $cols != 0)
				{
					$neededtds = $columns - $cols;
					for ($i = 0; $i < $neededtds; $i++)
					{
						$html .= '<td></td>';
					}
					$html .= '</tr>';
				}
				$headinclude .= $isrequiredjs;
				$headinclude .= "\nreturn (!haveerrors);\n}\n</script>\n";
			}
			else
			{
				$show['categoryfindertable'] = false;
				$html = '';
				$headinclude .= "<script type=\"text/javascript\">function validatecustomform(){return true;}</script>\n";
			}
		}
		// #### update mode ############################################
		else if ($mode == 'update')
		{
			$sql = $ilance->db->query("
				SELECT $field1
				FROM " . DB_PREFIX . $table1 . "
				WHERE visible = '1'
					$extracids
				ORDER BY sort
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$show['categoryfindertable'] = true;
				$c = 0;
				$headinclude .= "
<script type=\"text/javascript\">
function customImage(imagename, imageurl, errors)
{
        document[imagename].src = imageurl;
        if (!haveerrors && errors)
        {
                haveerrors = errors;
                alert_js(phrase['_please_fix_the_fields_marked_with_a_warning_icon_and_retry_your_action']);
        }
}
function validatecustomform(f)
{
        haveerrors = 0;
";
				$isrequiredjs = $isrequired = '';
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
					{
						$answertoinput = array ();
						$sql2 = $ilance->db->query("
							SELECT answer
							FROM " . DB_PREFIX . $table2 . "
							WHERE questionid = '" . $res['questionid'] . "'
								AND project_id = '" . intval($projectid) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0)
						{
							while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
							{
								$answertoinput[] = $res2['answer'];
							}
						}
						$answertoinput[0] = isset($answertoinput[0]) ? $answertoinput[0] : '';
						$formdefault = '';
						if (isset($res['formdefault']) AND $res['formdefault'] != '')
						{
							$formdefault = handle_input_keywords($res['formdefault']);
						}
						$overridejs = 0;
						switch ($res['inputtype'])
						{
							case 'yesno':
							{
								if (is_serialized($answertoinput[0]))
								{
									$answertoinput[0] = unserialize($answertoinput[0]);
								}
								if (!empty($answertoinput[0]) OR $answertoinput[0] == '0')
								{
									if ($answertoinput[0] == '1')
									{
										$input = '<label for="' . handle_input_keywords($res['formname']) . '1"><input type="radio" id="' . handle_input_keywords($res['formname']) . '1" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="1" checked="checked" /> ' . '{_yes}' . '</label><label for="' . handle_input_keywords($res['formname']) . '0"><input type="radio" id="' . handle_input_keywords($res['formname']) . '0" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="0" /> ' . '{_no}' . '</label>';
									}
									else
									{
										$input = '<label for="' . handle_input_keywords($res['formname']) . '1"><input type="radio" id="' . handle_input_keywords($res['formname']) . '1" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="1" /> ' . '{_yes}' . '</label><label for="' . handle_input_keywords($res['formname']) . '2"><input type="radio" id="' . handle_input_keywords($res['formname']) . '2" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="0" checked="checked" /> ' . '{_no}' . '</label>';
									}
								}
								else
								{
									$input = '<label for="' . handle_input_keywords($res['formname']) . '1"><input type="radio" id="' . handle_input_keywords($res['formname']) . '1" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="1" checked="checked" /> ' . '{_yes}' . '</label><label for="' . handle_input_keywords($res['formname']) . '2"><input type="radio" id="' . handle_input_keywords($res['formname']) . '2" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="0" /> ' . '{_no}' . '</label>';
								}
								$overridejs = 1;
								break;
							}
							case 'int':
							{
								if (is_serialized($answertoinput[0]))
								{
									$answertoinput[0] = unserialize($answertoinput[0]);
								}
								$input = '<input class="input" id="' . handle_input_keywords($res['formname']) . '" size="3" type="number" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="' . ilance_htmlentities($answertoinput[0]) . '" />';
								break;
							}
							case 'textarea':
							{
								$input = '<textarea id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" style="width:200px; height:44px; padding:2px" wrap="physical">' . handle_input_keywords($answertoinput[0]) . '</textarea>';
								$input .= ($categoryfindertable == false) ? '<br /><div style="width:250px;" class="smaller"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . handle_input_keywords($res['formname']) . '\', 100)"> {_increase_size}</a>&nbsp;&nbsp;<span class="smaller">|</span>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . handle_input_keywords($res['formname']) . '\', -100)"> {_decrease_size}</a></div>' : '';
								break;
							}
							case 'text':
							{
								$input = '<input class="input" type="text" id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="' . handle_input_keywords($answertoinput[0]) . '" />';
								break;
							}
							case 'url':
							{
								$input = '<input class="input" type="text" id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="' . handle_input_keywords($answertoinput[0]) . '" />';
								break;
							}
							case 'multiplechoice':
							{
								$sql2 = $ilance->db->query("
									SELECT choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
									FROM " . DB_PREFIX . $table3 . "
									WHERE questionid = '" . $res['questionid'] . "'
										AND visible = '1'
									ORDER BY sort
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql2) > 0)
								{
									if (is_serialized($answertoinput[0]))
									{
										$answertoinput = unserialize($answertoinput[0]);
									}
									$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}<br /><select style="width:250px; height:70px" multiple name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . '][]" id="' . handle_input_keywords($res['formname']) . '"><optgroup label="{_select}:">';
									while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
									{
										if (in_array(trim(ilance_htmlentities($res2['choice'])), $answertoinput))
										{
											$input .= '<option value="' . handle_input_keywords(trim($res2['choice'])) . '" selected="selected">' . handle_input_keywords(trim($res2['choice'])) . '</option>';
										}
										else
										{
											$input .= '<option value="' . handle_input_keywords(trim($res2['choice'])) . '">' . handle_input_keywords(trim($res2['choice'])) . '</option>';
										}
									}
									$input .= '</optgroup></select>';
								}
								break;
							}
							case 'pulldown':
							{
								$sql2 = $ilance->db->query("
									SELECT choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
									FROM " . DB_PREFIX . $table3 . "
									WHERE questionid = '" . $res['questionid'] . "'
										AND visible = '1'
									ORDER BY sort
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql) > 0)
								{
									if (is_serialized($answertoinput[0]))
									{
										$answertoinput = unserialize($answertoinput[0]);
									}
									$input = '<select class="select" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . '][]" id="' . handle_input_keywords($res['formname']) . '"><option value="">-</option>';
									while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
									{
										if (trim(ilance_htmlentities($res2['choice'])) == $answertoinput[0])
										{
											$input .= '<option value="' . handle_input_keywords(trim($res2['choice'])) . '" selected="selected">' . handle_input_keywords(trim($res2['choice'])) . '</option>';
										}
										else
										{
											$input .= '<option value="' . handle_input_keywords(trim($res2['choice'])) . '">' . handle_input_keywords(trim($res2['choice'])) . '</option>';
										}
									}
									$input .= '</select>';
								}
								break;
							}
						}
						$isrequired = '';
						if ($res['required'] AND $overridejs == 0)
						{
							$isrequired .= '<img name="' . stripslashes(handle_input_keywords($res['formname'])) . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="{_this_form_field_is_required}" />';
							$isrequiredjs .= "\n(fetch_js_object('" . stripslashes(handle_input_keywords($res['formname'])) . "').value.length < 1) ? customImage(\"" . stripslashes(handle_input_keywords($res['formname'])) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . stripslashes(handle_input_keywords($res['formname'])) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
						}
						if ($cols == 0)
						{
							$html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';
						}
						//$html .= '<td width="25%" valign="top"><div><strong>' . stripslashes(handle_input_keywords($res['question_' . $_SESSION['ilancedata']['user']['slng']])) . '</strong><div class="gray" style="padding-bottom:3px">' . stripslashes(handle_input_keywords($res['description_' . $_SESSION['ilancedata']['user']['slng']])) . '</div><div>' . $input . ' ' . $isrequired . '</div></div><div style="padding-bottom:7px"></div></td>';
						$html .= '<td width="25%" valign="top"><div>' . (($res['required']) ? '<span class="red" title="{_required}">*</span> ' : '') . '<strong>' . stripslashes(handle_input_keywords($res['question_' . $_SESSION['ilancedata']['user']['slng']])) . '</strong> ' . (!empty($res['description_' . $_SESSION['ilancedata']['user']['slng']]) ? '<a href="javascript:void(0)" onmouseover="Tip(\'<div>' . addslashes(handle_input_keywords($res['description_' . $_SESSION['ilancedata']['user']['slng']])) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>' : '') . '<div style="padding-top:3px">' . $input . ' ' . $isrequired . '</div></div><div style="padding-bottom:12px"></div></td>';
						$cols++;
						if ($cols == $columns)
						{
							$html .= '</tr>';
							$cols = 0;
						}
						$c++;
					}
				}
				if ($cols != $columns AND $cols != 0)
				{
					$neededtds = $columns - $cols;
					for ($i = 0; $i < $neededtds; $i++)
					{
						$html .= '<td></td>';
					}
					$html .= '</tr>';
				}
				$headinclude .= $isrequiredjs;
				$headinclude .= "\nreturn (!haveerrors);\n}\n</script>\n";
			}
			else
			{
				$show['categoryfindertable'] = false;
				$headinclude .= "<script type=\"text/javascript\">function validatecustomform(f) { return true; }</script>\n";
			}
		}
		// #### output mode ############################################
		else if ($mode == 'output')
		{
			$condition = (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0) ? "AND guests = '1'" : '';
			$show['itemspecifics'] = false;
			$sql = $ilance->db->query("
				SELECT $field1
				FROM " . DB_PREFIX . $table1 . "
				WHERE visible = '1'
					$extracids $condition
				ORDER BY sort
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$c = 0;
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
					{
						$answer = array();
						$sql2 = $ilance->db->query("
							SELECT answer
							FROM " . DB_PREFIX . $table2 . "
							WHERE questionid = '" . $res['questionid'] . "'
								AND project_id = '" . intval($projectid) . "'
								AND visible = '1'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0)
						{
							while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
							{
								$answer[] = $res2['answer'];
							}
						}
						if (isset($answer) AND count($answer) > 0)
						{
							$show['itemspecifics'] = true;
							if ($cols == 0)
							{
							    $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';
							}
							$html .= '<td width="25%" valign="top"><div style="padding-right:12px"><span class="gray">' . stripslashes(handle_input_keywords($res['question_' . $_SESSION['ilancedata']['user']['slng']])) . ':</span> <strong>';
							switch ($res['inputtype'])
							{
								case 'yesno':
								{
									$answer[0] = isset($answer[0]) ? $answer[0] : '';
									if ($answer[0] == 1)
									{
										$html .= '{_yes}';
									}
									else
									{
										$html .= '{_no}';
									}
									break;
								}
								case 'int':
								{
									$answer[0] = isset($answer[0]) ? $answer[0] : '';
									if ($answer[0] > 0)
									{
										$html .= ilance_htmlentities($answer[0]) . '&nbsp;';
									}
									else
									{
										$html .= '-';
									}
									break;
								}
								case 'textarea':
								{
									$answer[0] = isset($answer[0]) ? $answer[0] : '';
									$html .= htmlspecialchars_decode(ilance_htmlentities(stripslashes($answer[0]))) . '&nbsp;';
									break;
								}
								case 'text':
								{
									$answer[0] = isset($answer[0]) ? $answer[0] : '';
									$html .= htmlspecialchars_decode(ilance_htmlentities(stripslashes($answer[0]))) . '&nbsp;';
									break;
								}
								case 'url':
								{
									$answer[0] = isset($answer[0]) ? ilance_htmlentities(stripslashes(trim($answer[0]))) : '';
									$answer[0] = (mb_substr($answer[0], 0, 7) == 'http://') ? mb_substr($answer[0], 7) : $answer[0];
									$html .= '<a href="http://' . $answer[0] . '" target="_blank">' . $answer[0] . '</a>&nbsp;';
									break;
								}
								case 'multiplechoice':
								{
									if (is_serialized($answer[0]))
									{
										$answer = unserialize($answer[0]);
									}
									$fix = '';
									foreach ($answer AS $answered)
									{
										$fix .= htmlspecialchars_decode(ilance_htmlentities(stripslashes($answered))) . ', ';
									}
									$html .= mb_substr($fix, 0, -2);
									break;
								}
								case 'pulldown':
								{
									if (is_serialized($answer[0]))
									{
										$answer = unserialize($answer[0]);
									}
									$fix = '';
									foreach ($answer AS $answered)
									{
										$fix .= htmlspecialchars_decode(ilance_htmlentities(stripslashes($answered)));
									}
									if (empty($fix))
									{
										if ($type == 'product')
										{
											$html .= ($show['is_owner'] ? '[ <span style="" class="smaller blue"><a href="' . $ilpage['selling'] . '?cmd=product-management&amp;state=' . $type . '&amp;id=' . intval($ilance->GPC['id']) . '#categoryfinder">{_edit}</a></span> ]' : '-');
										}
										else
										{
											$html .= ($show['is_owner'] ? '[ <span style="" class="smaller blue"><a href="' . $ilpage['buying'] . '?cmd=rfp-management&amp;state=' . $type . '&amp;id=' . intval($ilance->GPC['id']) . '#categoryfinder">{_edit}</a></span> ]' : '-');
										}
									}
									else
									{
										$html .= $fix;
									}
									break;
								}
						}
						$html .= '</strong></div></td>';
						$cols++;
						$c++;
						if ($cols == $columns)
						{
							$html .= '</tr><tr><td style="padding:6px" colspan="' . $columns . '"></td></tr>';
							$cols = 0;
						}
					    }
					}
				}
				if ($cols != $columns AND $cols != 0)
				{
					$neededtds = $columns - $cols;
					for ($i = 0; $i < $neededtds; $i++)
					{
						$html .= '<td></td>';
					}
					$html .= '</tr>';
				}
			}
		}
		// #### output mini-mode for search results example: <span class="label">Make:</span> <span class="value">Ford</span>
		else if ($mode == 'outputmini')
		{
			$condition = (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0) ? "AND guests = '1'" : '';
			$sql = $ilance->db->query("
				SELECT $field1
				FROM " . DB_PREFIX . $table1 . "
				WHERE visible = '1'
					$extracids $condition
				ORDER BY sort
				LIMIT $outputlimit
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$c = 0;
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
					{
						$answer = array();
						$sql2 = $ilance->db->query("
							SELECT answer
							FROM " . DB_PREFIX . $table2 . "
							WHERE questionid = '" . $res['questionid'] . "'
								AND project_id = '" . intval($projectid) . "'
								AND visible = '1'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0)
						{
							while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
							{
								if (!empty($res2['answer']))
								{
									$answer[] = $res2['answer'];
								}
							}
						}
						if (isset($answer) AND count($answer) > 0)
						{
							$html .= '<span class="label" title="' . stripslashes(handle_input_keywords($res['question_' . $_SESSION['ilancedata']['user']['slng']])) . '">' . stripslashes(handle_input_keywords($res['question_' . $_SESSION['ilancedata']['user']['slng']])) . ':</span> <span class="value" title="';
							switch ($res['inputtype'])
							{
								case 'yesno':
								{
									$answer[0] = isset($answer[0]) ? $answer[0] : '';
									if ($answer[0] == 1)
									{
										$html .= '{_yes}">{_yes}';
									}
									else
									{
										$html .= '{_no}">{_no}';
									}
									break;
								}
								case 'int':
								{
									$answer[0] = isset($answer[0]) ? $answer[0] : '';
									if ($answer[0] > 0)
									{
										$html .= ilance_htmlentities($answer[0]) . '">' . ilance_htmlentities($answer[0]) . '&nbsp;';
									}
									else
									{
										$html .= '">-';
									}
									break;
								}
								case 'textarea':
								{
									$answer[0] = isset($answer[0]) ? $answer[0] : '';
									$html .= htmlspecialchars_decode(ilance_htmlentities(stripslashes($answer[0]))) . '">' . htmlspecialchars_decode(ilance_htmlentities(stripslashes($answer[0]))) . '&nbsp;';
									break;
								}
								case 'text':
								{
									$answer[0] = isset($answer[0]) ? $answer[0] : '';
									$html .= htmlspecialchars_decode(ilance_htmlentities(stripslashes($answer[0]))) . '">' . htmlspecialchars_decode(ilance_htmlentities(stripslashes($answer[0]))) . '&nbsp;';
									break;
								}
								case 'url':
								{
									$answer[0] = isset($answer[0]) ? ilance_htmlentities(stripslashes(trim($answer[0]))) : '';
									$answer[0] = (mb_substr($answer[0], 0, 7) == 'http://') ? mb_substr($answer[0], 7) : $answer[0];
									$html .= 'http://' . $answer[0] . '"><a href="http://' . $answer[0] . '" target="_blank">' . $answer[0] . '</a>&nbsp;';
									break;
								}
								case 'multiplechoice':
								{
									if (is_serialized($answer[0]))
									{
										$answer = unserialize($answer[0]);
									}
									$fix = '';
									foreach ($answer AS $answered)
									{
										$fix .= htmlspecialchars_decode(ilance_htmlentities(stripslashes($answered))) . ', ';
									}
									$html .= mb_substr($fix, 0, -2) . '">' . mb_substr($fix, 0, -2);
									break;
								}
								case 'pulldown':
								{
									if (is_serialized($answer[0]))
									{
										$answer = unserialize($answer[0]);
									}
									$fix = '';
									foreach ($answer AS $answered)
									{
										$fix .= htmlspecialchars_decode(ilance_htmlentities(stripslashes($answered)));
									}
									$html .= $fix . '">' . $fix;
									break;
								}
							}
							$html .= '</span>';
						}
					}
				}
			}
		}
		// #### output mode ############################################
		else if ($mode == 'api')
		{
			$sql = $ilance->db->query("
				SELECT $field1
				FROM " . DB_PREFIX . $table1 . "
				WHERE visible = '1'
					$extracids
				ORDER BY sort
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$show['categoryfindertable'] = true;
				$num = 0;
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
					{
						$formdefault = '';
						if (isset($res['formdefault']) AND $res['formdefault'] != '')
						{
							$formdefault = handle_input_keywords($res['formdefault']);
						}
						$overridejs = 0;
						switch ($res['inputtype'])
						{
							case 'yesno':
							{
								$input = '<label for="' . handle_input_keywords($res['formname']) . '1"><input type="radio" id="' . handle_input_keywords($res['formname']) . '1" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="1" checked="checked"> {_yes} </label> <label for="' . handle_input_keywords($res['formname']) . '0"><input type="radio" id="' . handle_input_keywords($res['formname']) . '0" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="0"> {_no}</label>';
								$inputdothis = '{_enter_the_number} <strong>' . $res['questionid'] . '</strong> {_followed_by_equal_symbol_followed_by_a_one_or_zero}';
								$inputexample = $res['questionid'] . '=1 {_or_lower} ' . $res['questionid'] . '=0';
								$overridejs = 1;
								break;
							}
							case 'int':
							{
								$input = '<input class="input" size="3" type="number" id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="' . $formdefault . '" />';
								$inputdothis = '{_enter_the_number} <strong>' . $res['questionid'] . '</strong> {_followed_by_equal_symbol_followed_by_your_number}';
								$inputexample = $res['questionid'] . '=10 {_or_lower} ' . $res['questionid'] . '=' . number_format(rand(1000, 500000)) . ' {_or_lower} ' . $res['questionid'] . '=' . date('Y');
								break;
							}
							case 'textarea':
							case 'text':
							{
								$input = '<input class="input" type="text" id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="' . $formdefault . '" />';
								$inputdothis = '{_enter_the_number} <strong>' . $res['questionid'] . '</strong> {_followed_by_equal_symbol_followed_by_your_answer}';
								$inputexample = $res['questionid'] . '={_my_answer}';
								break;
							}
							case 'url':
							{
								$input = '<input class="input" type="text" id="' . handle_input_keywords($res['formname']) . '" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . ']" value="' . $formdefault . '" />';
								$inputdothis = '{_enter_the_number} <strong>' . $res['questionid'] . '</strong> {_followed_by_equal_symbol_followed_by_a_url}';
								$inputexample = $res['questionid'] . '=http://www.url.com';
								break;
							}
							case 'multiplechoice':
							{
								$sql2 = $ilance->db->query("
									SELECT choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
									FROM " . DB_PREFIX . $table3 . "
									WHERE questionid = '" . $res['questionid'] . "'
										AND visible = '1'
									ORDER BY sort
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql) > 0)
								{
									$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}<br /><select style="width:250px; height:70px" multiple name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . '][]" id="' . handle_input_keywords($res['formname']) . '"><option value="">-</option><optgroup label="{_select}:">';
									while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
									{
										if ($res2['choice'] != '')
										{
											$input .= '<option value="' . trim(ilance_htmlentities($res2['choice'])) . '">' . trim(ilance_htmlentities($res2['choice'])) . '</option>';
											$cchoice = trim(ilance_htmlentities($res2['choice']));
										}
									}
									$input .= '</optgroup></select>';
									$inputdothis = '{_enter_the_number} <strong>' . $res['questionid'] . '</strong> {_followed_by_equal_symbol_followed_by_a_single_answer_in_pulldown_additional_answers}';
									$inputexample = $res['questionid'] . '=' . $cchoice . ' or ' . $res['questionid'] . '={_choice_one_comma_space}';
								}
								break;
							}
							case 'pulldown':
							{
								$sql2 = $ilance->db->query("
									SELECT choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
									FROM " . DB_PREFIX . $table3 . "
									WHERE questionid = '" . $res['questionid'] . "'
										AND visible = '1'
									ORDER BY sort
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql) > 0)
								{
									$input = '<select class="select" name="custom[' . $res['questionid'] . '][' . handle_input_keywords($res['formname']) . '][]" id="' . handle_input_keywords($res['formname']) . '"><option value="">-</option><optgroup label="{_select}:">';
									while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
									{
										if ($res2['choice'] != '')
										{
											$input .= '<option value="' . trim(ilance_htmlentities($res2['choice'])) . '">' . trim(ilance_htmlentities($res2['choice'])) . '</option>';
											$cchoice = trim(ilance_htmlentities($res2['choice']));
										}
									}
									$input .= '</optgroup></select>';
									$inputdothis = '{_enter_the_number} <strong>' . $res['questionid'] . '</strong> {_followed_by_equal_symbol_followed_by_a_single_answer_in_pulldown}';
									$inputexample = $res['questionid'] . '=' . $cchoice;
								}
								break;
							}
						}
						if ($cols == 0)
						{
							$html .= '<tr class="alt1">';
						}
						$html .= '<td width="20%" valign="top"><div><strong>' . handle_input_keywords($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong> ' . (!empty($res['description_' . $_SESSION['ilancedata']['user']['slng']]) ? '<a href="javascript:void(0)" onmouseover="Tip(\'<div>' . addslashes(handle_input_keywords($res['description_' . $_SESSION['ilancedata']['user']['slng']])) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>' : '') . '</div><div style="padding-top:3px">' . $input . '</div><div style="padding-bottom:7px"></div></td>';
						$html .= '<td width="60%" valign="top"><div class="black">' . $inputdothis . '</div></td>';
						$html .= '<td width="20%" valign="top"><strong>' . $inputexample . '</strong></td>';
						$cols++;
						if ($cols == $columns)
						{
							$html .= '</tr>';
							$cols = 0;
						}
					}
				}
				if ($cols != $columns AND $cols != 0)
				{
					$neededtds = $columns - $cols;
					for ($i = 0; $i < $neededtds; $i++)
					{
						$html .= '<td></td>';
					}
					$html .= '</tr>';
				}
			}
			else
			{
				$show['categoryfindertable'] = false;
				$html = '';
			}
			if ($html != '')
			{
				$ilance->template->templateregistry['questions'] = '<table border="0" cellpadding="12" cellspacing="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '"><tr class="alt3"><td nowrap="nowrap"><strong>{_to_use_this_specific}</strong></td><td><strong>{_do_this_in_your_csv_column_for_attributes}</strong></td><td><strong>{_example}</strong></td></tr>' . $html . '</table>';
				$html = $ilance->template->parse_template_phrases('questions');
			}
		}
		if ($html != '' AND $mode != 'outputmini')
		{
			$ilance->template->templateregistry['questions'] = '<table border="0" cellpadding="0" cellspacing="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">' . $html . '</table>';
			$html = $ilance->template->parse_template_phrases('questions');
		}
		return $html;
	}

	/**
	* Function to determine if a question is multiple choice
	*
	* @param       integer        question id
	* @param       string         mode (register, project, product or profile)
	*
	* @return      boolean        Returns true or false
	*/
	function is_question_multiplechoice($qid = 0, $mode = '')
	{
		global $ilance;
		if (empty($mode) OR $qid == 0)
		{
			return 0;
		}
		switch ($mode)
		{
			case 'register':
			{
				$table = DB_PREFIX . 'register_questions';
				break;
			}
			case 'project':
			{
				$table = DB_PREFIX . 'project_questions';
				break;
			}
			case 'product':
			{
				$table = DB_PREFIX . 'product_questions';
				break;
			}
			case 'profile':
			{
				$table = DB_PREFIX . 'profile_questions';
				break;
			}
		}
		$sql = $ilance->db->query("
			SELECT inputtype
			FROM $table
			WHERE questionid = '" . intval($qid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['inputtype'] == 'multiplechoice')
			{
				return 1;
			}
		}
		return 0;
	}
	
	/**
	* Function to print a category question pull down menu with values admins created based on other category questions
	* allowing chained pull down menus and association when sellers post new items into various category where category specifics are defined.
	*
	* @param       integer       category id
	* @param       integer       question id
	* @param       integer       option id
	* @param       string        mode (product, service)
	* @param       string        short language identifier (default eng)
	* @param       string        type of action (insert, update)
	* @param       string        fieldname
	* @param       integer       counter
	* @param       integer       default option id selected
	*
	* @return      string        HTML representation of the custom listing questions
	*/
	function print_category_question_pulldown_groups($cid = 0, $qid = 0, $optionid = 0, $mode = 'product', $slng = 'eng', $type = 'insert', $fieldname = 'multiplechoicegroup', $counter = 0, $selectedoptionid = 0)
	{
		global $ilance, $ilconfig, $ilpage;
		if ($type == 'insert')
		{
			$html = '<select name="' . $fieldname . '[]" id="' . $fieldname . '_' . $counter . '" class="select"><option value="0">-</option>';
		}
		else
		{
			if ($optionid > 0)
			{
				$html = '<select name="' . $fieldname . '[' . $optionid . ']" id="' . $fieldname . '_' . $optionid . '" class="select"><option value="0">-</option>';
			}
			else
			{
				$html = '<select name="' . $fieldname . '[]" id="' . $fieldname . '_' . $counter . '" class="select"><option value="0">-</option>';
			}
		}
		$table = DB_PREFIX . 'product_questions';
		$table2 = DB_PREFIX . 'product_questions_choices';
		if ($mode == 'service')
		{
			$table = DB_PREFIX . 'project_questions';
			$table2 = DB_PREFIX . 'project_questions_choices';
		}
		$sql = $ilance->db->query("
			SELECT questionid, question_$slng AS question, description_$slng AS description
			FROM $table
			WHERE cid = '" . intval($cid) . "'
				AND visible = '1'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($res['questionid'] != $qid OR $qid == 0)
				{
					$html .= '<optgroup label="' . handle_input_keywords($res['question']) . ' (' . handle_input_keywords($res['description']) . ')">';
					$sql2 = $ilance->db->query("
						SELECT optionid, parentoptionid, choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
						FROM $table2
						WHERE questionid = '" . $res['questionid'] . "'
							AND visible = '1'
						ORDER BY sort ASC
					");
					if ($ilance->db->num_rows($sql2) > 0)
					{
						while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
						{
							$parentgroup = (($res2['parentoptionid'] > 0) ? $ilance->db->fetch_field($table2, "optionid = '" . $res2['parentoptionid'] . "'", 'choice_' . $_SESSION['ilancedata']['user']['slng']) : '');
							if (!empty($parentgroup))
							{
								$parentgroup = " ($parentgroup)";
							}
							if ($selectedoptionid == $res2['optionid'])
							{
								$html .= '<option value="' . $res2['optionid'] . '" selected="selected">' . handle_input_keywords($res2['choice']) . $parentgroup . '</option>';
							}
							else
							{
								$html .= '<option value="' . $res2['optionid'] . '">' . handle_input_keywords($res2['choice']) . $parentgroup . '</option>';
							}
						}
					}
					$html .= '</optgroup>';
				}
			}
		}
		$html .= '</select>';
		return $html;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>