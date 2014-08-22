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
* Profile questions class to perform displaying and updating for profile questions within ILance.
*
* @package      iLance\ProfileQuestions
* @version      4.0.0.8059
* @author       ILance
*/
class profile_questions
{
	/**
	* Function for displaying custom profile questions based on the pages within profile the admin has predefined.
	*
	* @param       integer       page number
	* @param       string        mode (input, updateprofile, updateprofileadmin, update and output1)
	* @param       integer       user id
	*
	* @return      string        HTML representation of the question profile question
	*/
	function construct_profile_questions($userid = 0, $mode = '')
	{
		global $ilance, $phrase, $headinclude, $ilconfig, $ilpage;
		$html = '';
		if ($mode == 'input')
		{	
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_questions
				WHERE user_id = '" . intval($userid) . "'
					AND visible = '1'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$c = 0;
				$html = $isrequiredjs = $isrequired = '';
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
				while ($res = $ilance->db->fetch_array($sql))
				{
					if (isset($res['formdefault']) AND $res['formdefault'] != '')
					{
						$formdefault = $res['formdefault'];
					}
					else
					{
					    $formdefault = '';
					}
					$overridejs = 0;
					switch ($res['inputtype'])
					{
						case 'yesno':
						{
							$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" checked="checked"> {_yes}</label> <label for="' . $res['questionid'] . '0"><input type="radio" id="' . $res['questionid'] . '0" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0"> {_no}</label>';
							$overridejs = 1;
							break;
						}
						case 'int':
						{
							$input = '<input class="input" size="3" type="text" id="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="' . $formdefault . '" style="font-family: verdana" />';
							break;
						}
						case 'textarea':
						{
							//$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['questionid'] . '" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $formdefault . '</textarea><br /> <div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', 100)">'.'{_increase_size}'.'</a>&nbsp; <a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', -100)">'.'{_decrease_size}'.'</a></div></div>';
							$input = '<style id="wysiwyg_html" type="text/css">
<!--
' . $ilance->styles->css_cache['csswysiwyg'] . '
//-->
</style>
<div class="ilance_wysiwyg">
<table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
<td class="wysiwyg_wrapper" align="right" height="25">

	<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
	<tr>
		<td width="100%" align="left" class="smaller">' . '{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}' . '</td>
		<td>
				<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . '{_decrease_size}' . '" border="0" /></a></div>
				<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . '{_increase_size}' . '" border="0" /></a></div>
		</td>
		<td style="padding-right:15px"></td>
	</tr>
	</table>
</td>
</tr>
	<tr>
		<td><textarea id="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $formdefault . '</textarea></td>
	</tr>
</table>
</div>';
							break;
						}
						case 'text':
						{
							$input = '<input class="input" type="text" id="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="' . $formdefault . '" style="font-family: verdana" />';
							break;
						}
						case 'multiplechoice':
						{
							if (!empty($res['multiplechoice']))
							{
								$choices = explode('|', $res['multiplechoice']);
								$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}' . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']">';
								$input .= '<option value="">-</option>';
								$input .= '<optgroup name="custom[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" label="' . '{_select}' . '" >';
								foreach ($choices as $choice)
								{
									if (!empty($choice))
									{
										$default = ($choice == $res['formdefault']) ? ' selected' : '';
										$input .= '<option value="' . trim(ilance_htmlentities($choice)) . '"' . $default . '>' . trim(ilance_htmlentities($choice)) . '</option>';
									}
								}
								$input .= '</optgroup>';
								$input .= '</select>';
							}
							break;
						}
						case 'pulldown':
						{
							if (!empty($res['multiplechoice']))
							{
								$choices = explode('|', $res['multiplechoice']);
								$input = '<select name="custom[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" style="font-family: verdana">';
								foreach ($choices as $choice)
								{
									if (!empty($choice))
									{
										$default = ($choice == $res['formdefault']) ? ' selected' : '';
										$input .= '<option value="' . trim(ilance_htmlentities($choice)) . '"' . $default . '>' . trim(ilance_htmlentities($choice)) . '</option>';
									}
								}
								$input .= '</select>';
							}
							break;
						}
					}
					if ($res['required'] AND $overridejs == 0)
					{
						$questionid = $res['questionid'];
						$questionid = $res['questionid'];
						if (isset($_POST['custom'][$questionid][$questionid]))
						{
							if ((is_array($_POST['custom'][$questionid][$questionid]) AND empty($_POST['custom'][$questionid][$questionid]['0']) AND $_POST['custom'][$questionid][$questionid]['0'] != '0') OR (empty($_POST['custom'][$questionid][$questionid]) AND $_POST['custom'][$questionid][$questionid] != '0'))
							{
								$isrequired = '<img name="custom[' . $res["questionid"] . '][' . $res["questionid"] . ']error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif" width="21" height="13" border="0" alt="' . '{_this_form_field_is_required}' . '" />';
							}
							else
							{
								$isrequired = '<img name="custom[' . $res["questionid"] . '][' . $res["questionid"] . ']error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . '{_this_form_field_is_required}' . '" />';
							}
						}
					}
					else
					{
						$isrequired = '';
					}
					$html .= '<table width="100%"  border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td><div><strong>' . stripslashes($res['question']) . '</strong></div></td>
</tr>
<tr>
	<td><div class="gray" style="padding-bottom:3px">' . stripslashes($res['description']) . '</div>' . $input . ' ' . $isrequired . '</td>
</tr>
</table>
<div style="padding-bottom:9px"></div>';
					$c++;
				}
				$headinclude .= $isrequiredjs;
				$headinclude .= "\nreturn (!haveerrors);\n}\n</script>\n";
			}
			else
			{
				$html = '';
				$headinclude .= "<script type=\"text/javascript\">function validatecustomform(f){return true;}</script>\n";
			}
		}
		else if ($mode == 'updateprofile')
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_questions
				WHERE user_id = " . intval($ilance->GPC['id']) . " 
				AND visible = '1'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$c = 0;
				$html = $isrequiredjs = $isrequired = '';
				$headinclude .= "
<script type=\"text/javascript\">
<!--
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
				while ($res = $ilance->db->fetch_array($sql))
				{
					// do we have an answer?
					$answertoinput = $formdefault = '';
					$sql2 = $ilance->db->query("
						SELECT answer
						FROM " . DB_PREFIX . "profile_answers
						WHERE questionid = '" . $res['questionid'] . "'
							AND user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql2) > 0)
					{
						$res2 = $ilance->db->fetch_array($sql2);
						$answertoinput = stripslashes($res2['answer']);
					}
					if (!empty($res['formdefault']))
					{
						$formdefault = $res['formdefault'];
					}
					switch ($res['inputtype'])
					{
						case 'yesno':
						{
							if ($answertoinput != '')
							{
								if ($answertoinput == 1)
								{
									$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" checked="checked" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" /> {_no}</label>';
								}
								else
								{
									$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" checked="checked" /> {_no}</label>';
								}
							}
							else
							{
								$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" checked="checked" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" /> {_no}</label>';
							}
							break;
						}
						case 'int':
						{
							$input = '<input class="input" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
							break;
						}
						case 'textarea':
						{
							//$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['questionid'] . '" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $answertoinput . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', 100)">'.'{_increase_size}'.'</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', -100)">'.'{_decrease_size}'.'</a></div></div>';
							$input = '<style id="wysiwyg_html" type="text/css">
<!--
' . $ilance->styles->css_cache['csswysiwyg'] . '
//-->
</style>
<div class="ilance_wysiwyg">
<table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
<td class="wysiwyg_wrapper" align="right" height="25">

	<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
	<tr>
		<td width="100%" align="left" class="smaller">' . '{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}' . '</td>
		<td>
				<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . '{_decrease_size}' . '" border="0" /></a></div>
				<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . '{_increase_size}' . '" border="0" /></a></div>
		</td>
		<td style="padding-right:15px"></td>
	</tr>
	</table>
</td>
</tr>
	<tr>
		<td><textarea id="' . $res['questionid'] . '" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea></td>
	</tr>
</table>
</div>';
							break;
						}
						case 'text':
						{
							$input = '<input class="input" type="text" id="' . $res['questionid'] . '" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
							break;
						}
						case 'multiplechoice':
						{
							if (!empty($res['multiplechoice']))
							{
								$choices = explode('|', $res['multiplechoice']);
								$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}' . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="' . $res['questionid'] . '">';
								if (is_serialized($answertoinput))
								{
									$answers = unserialize($answertoinput);
								}
								if (empty($answers))
								{
									$answers = array ();
								}
								$input .= '<optgroup label="' . '{_select}' . '">';
								foreach ($choices as $choice)
								{
									if (in_array($choice, $answers))
									{
										$input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
									}
									else
									{
										$input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
									}
								}
								$input .= '</optgroup>';
								$input .= '</select>';
							}
							break;
						}
						case 'pulldown':
						{
							if (!empty($res['multiplechoice']))
							{
								$choices = explode('|', $res['multiplechoice']);
								$input = '<select name="custom[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="' . $res['questionid'] . '" style="font-family: verdana">';
								if (is_serialized($answertoinput))
								{
									$answers = unserialize($answertoinput);
								}
								if (empty($answers))
								{
									$answers = array ();
								}
								foreach ($choices as $choice)
								{
									if (in_array($choice, $answers))
									{
										$input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
									}
									else
									{
										$input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
									}
								}
								$input .= '</select>';
							}
							break;
						}
					}
					if ($res['required'])
					{
						$isrequired = '<img name="' . $res['questionid'] . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . '{_this_form_field_is_required}' . '" />';
						$isrequiredjs .= "\n (fetch_js_object('" . $res['questionid'] . "').value.length < 1) ? customImage(\"" . $res['questionid'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . $res['questionid'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
					}
					else
					{
						$isrequired = '';
					}
					$html .= '<div style="padding-bottom:9px"><div><strong>' . stripslashes($res['question']) . '</strong></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td colspan="3"><div class="gray">' . stripslashes($res['description']) . ' ' . $isrequired . '</div></td>
</tr>
<tr>
	<td align="left" height="33">' . $input . '</td>
</tr>
</table></div>';
					$c++;
				}
				$headinclude .= $isrequiredjs;
				$headinclude .= "\nreturn (!haveerrors);\n}\n//-->\n</script>\n";
			}
			else
			{
				$html = '';
				$headinclude .= "<script type=\"text/javascript\">\n<!--\nfunction validatecustomform(f){return true;}\n//-->\n</script>\n";
			}
		}
		else if ($mode == 'updateprofileadmin')
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_questions
				WHERE visible = 1
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$c = 0;
				$html = '';
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					// only selecting actual questions that have been answered by this user
					$answertoinput = '';
					$sql2 = $ilance->db->query("
						SELECT answerid, answer
						FROM " . DB_PREFIX . "profile_answers
						WHERE questionid = '" . $res['questionid'] . "'
							AND user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql2) > 0)
					{
						$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
						$answertoinput = stripslashes($res2['answer']);
						$formdefault = '';
						if (!empty($res['formdefault']))
						{
							$formdefault = $res['formdefault'];
						}
						switch ($res['inputtype'])
						{
							case 'yesno':
							{
								if ($answertoinput != '')
								{
									if ($answertoinput == 1)
									{
										$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" checked="checked" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" /> {_no}</label>';
									}
									else
									{
										$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" checked="checked" /> {_no}</label>';
									}
								}
								else
								{
									$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" checked="checked" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" /> {_no}</label>';
								}
								break;
							}
							case 'int':
							{
								$input = '<input class="input" size="3" type="text" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
								break;
							}
							case 'textarea':
							{
								$input = '<textarea id="' . $res['questionid'] . '" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" style="width:250px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea>';
								break;
							}
							case 'text':
							{
								$input = '<input class="input" type="text" id="' . $res['questionid'] . '" name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
								break;
							}
							case 'multiplechoice':
							{
								if (!empty($res['multiplechoice']))
								{
									$choices = explode('|', $res['multiplechoice']);
									$input = '<select style="width:250px; height:70px; font-family: verdana" multiple name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="' . $res['questionid'] . '">';
									$answers = array ();
									if (is_serialized($answertoinput))
									{
										$answers = unserialize($answertoinput);
									}
									if (empty($answers))
									{
										$answers = array ();
									}
									foreach ($choices AS $choice)
									{
										if (in_array($choice, $answers))
										{
											$input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
										}
										else
										{
											$input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
										}
									}
									$input .= '</select>';
								}
								break;
							}
							case 'pulldown':
							{
								if (!empty($res['multiplechoice']))
								{
									$choices = explode('|', $res['multiplechoice']);
									$input = '<select name="custom2[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="' . $res['questionid'] . '" style="font-family: verdana">';
									if (is_serialized($answertoinput))
									{
										$answers = unserialize($answertoinput);
									}
									if (empty($answers))
									{
										$answers = array ();
									}
									foreach ($choices AS $choice)
									{
										if (in_array($choice, $answers))
										{
											$input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
										}
										else
										{
											$input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
										}
									}
									$input .= '</select>';
								}
								break;
							}
						}
						$html .= '<tr class="alt1" valign="top">
	<td><span class="gray">' . handle_input_keywords(stripslashes($res['question'])) . '</span></td>
	<td><div><span style="float:right;padding-left:6px" class="smaller blueonly"><a href="' . $ilpage['subscribers'] . '?subcmd=_remove-profile-answer&amp;id=' . $res2['answerid'] . '&amp;uid=' . intval($userid) . '" target="_self" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">{_remove_this_answer}</a></span>' . $input . '</div></td>
</tr>';
						$c++;
					}
				}
			}
			else
			{
				$html = '';
			}
		}
		else if ($mode == 'update')
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_questions
				WHERE visible = '1'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$c = 0;
				$html = $isrequiredjs = '';
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
				while ($res = $ilance->db->fetch_array($sql))
				{
					$answertoinput = '';
					$sql2 = $ilance->db->query("
						SELECT answer
						FROM " . DB_PREFIX . "profile_answers
						WHERE questionid = '" . $res['questionid'] . "'
							AND user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql2) > 0)
					{
						$res2 = $ilance->db->fetch_array($sql2);
						$answertoinput = stripslashes($res2['answer']);
					}
					$formdefault = '';
					if (isset($res['formdefault']) AND $res['formdefault'] != '')
					{
						$formdefault = $res['formdefault'];
					}
					switch ($res['inputtype'])
					{
						case 'yesno':
						{
							if ($answertoinput != '')
							{
								if ($answertoinput == 1)
								{
									$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" checked="checked" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" /> {_no}</label>';
								}
								else
								{
									$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" checked="checked" /> {_no}</label>';
								}
							}
							else
							{
								$input = '<label for="' . $res['questionid'] . '1"><input type="radio" id="' . $res['questionid'] . '1" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="1" checked="checked" /> {_yes}</label><label for="' . $res['questionid'] . '2"><input type="radio" id="' . $res['questionid'] . '2" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="0" /> {_no}</label>';
							}
							break;
						}
						case 'int':
						{
							$input = '<input class="input" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
							break;
						}
						case 'textarea':
						{
							$input = '<style id="wysiwyg_html" type="text/css">
<!--
' . $ilance->styles->css_cache['csswysiwyg'] . '
//-->
</style>
<div class="ilance_wysiwyg">
<table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
<td class="wysiwyg_wrapper" align="right" height="25">

	<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
	<tr>
		<td width="100%" align="left" class="smaller">' . '{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}' . '</td>
		<td>
				<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . '{_decrease_size}' . '" border="0" /></a></div>
				<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['questionid'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . '{_increase_size}' . '" border="0" /></a></div>
		</td>
		<td style="padding-right:15px"></td>
	</tr>
	</table>
</td>
</tr>
	<tr>
		<td><textarea id="' . $res['questionid'] . '" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea></td>
	</tr>
</table>
</div>';
							break;
						}
						case 'text':
						{
							$input = '<input class="input" type="text" id="' . $res['questionid'] . '" name="custom[' . $res['questionid'] . '][' . $res['questionid'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
							break;
						}
						case 'multiplechoice':
						{
							if (!empty($res['multiplechoice']))
							{
								$choices = explode('|', $res['multiplechoice']);
								$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}' . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="' . $res['questionid'] . '">';
								if (is_serialized($answertoinput))
								{
									$answers = unserialize($answertoinput);
								}
								if (empty($answers))
								{
									$answers = array ();
								}
								$input .= '<optgroup label="' . '{_select}' . '">';
								foreach ($choices as $choice)
								{
									if (in_array($choice, $answers))
									{
										$input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
									}
									else
									{
										$input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
									}
								}
								$input .= '</optgroup>';
								$input .= '</select>';
							}
							break;
						}
						case 'pulldown':
						{
							if (!empty($res['multiplechoice']))
							{
								$choices = explode('|', $res['multiplechoice']);
								$input = '<select name="custom[' . $res['questionid'] . '][' . $res['questionid'] . '][]" id="' . $res['questionid'] . '" style="font-family: verdana">';
								if (is_serialized($answertoinput))
								{
									$answers = unserialize($answertoinput);
								}
								if (empty($answers))
								{
									$answers = array ();
								}
								foreach ($choices as $choice)
								{
									if (in_array($choice, $answers))
									{
										$input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
									}
									else
									{
										$input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
									}
								}
								$input .= '</select>';
							}
							break;
						}
					}
					if ($res['required'])
					{
						$isrequired = '<img name="' . $res['questionid'] . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . '{_this_form_field_is_required}' . '" />';
						$isrequiredjs .= "\n(fetch_js_object('" . $res['questionid'] . "').value.length < 1) ? customImage(\"" . $res['questionid'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . $res['questionid'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
					}
					else
					{
						$isrequired = '';
					}
					$html .= '<tr>
	<td colspan="5">
	<table width="100%" border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
	<tr>
		<td width="50%" valign="top">

			<fieldset class="fieldset" style="margin:0px">
			<legend>' . stripslashes($res['question']) . '</legend>
			<table width="100%" border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
			<tr>
				<td colspan="3">' . stripslashes($res['description']) . ' ' . $isrequired . '</td>
			</tr>
			<tr>
				<td align="left" height="33">' . $input . '</td>
			</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	</table>
	</td>
</tr>';
					$c++;
				}
				$headinclude .= $isrequiredjs;
				$headinclude .= "\nreturn (!haveerrors);\n}\n</script>\n";
			}
			else
			{
				$html = '';
				$headinclude .= "<script type=\"text/javascript\">function validatecustomform(f){return true;}</script>\n";
			}
		}
		else if ($mode == 'output1')
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_questions
				WHERE visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$c = 0;
				$html = '';
				while ($res = $ilance->db->fetch_array($sql))
				{
					$sql2 = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "profile_answers
						WHERE questionid = '" . $res['questionid'] . "'
							AND user_id = '" . intval($userid) . "'
							AND visible = '1'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql2) > 0)
					{
						$res2 = $ilance->db->fetch_array($sql2);
						$html .= '<tr><td colspan="4" align="left"><fieldset class="fieldset" style="margin:0px"><legend>' . stripslashes($res['question']) . '</legend>';
						switch ($res['inputtype'])
						{
							case 'yesno':
							{
								if ($res2['answer'] == 1)
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
								$html .= intval($res2['answer']) . '&nbsp;';
								break;
							}
							case 'textarea':
							{
								$html .= stripslashes($res2['answer']) . '&nbsp;';
								break;
							}
							case 'text':
							{
								$html .= stripslashes($res2['answer']) . '&nbsp;';
								break;
							}
							case 'multiplechoice':
							{
								if (!empty($res2['answer']))
								{
									if (is_serialized($res2['answer']))
									{
										$answers = unserialize($res2['answer']);
									}
									if (empty($answers))
									{
										$answers = array ();
									}
									$fix = '';
									foreach ($answers as $answered)
									{
										$fix .= stripslashes($answered) . ', ';
									}
									$html .= mb_substr($fix, 0, -2);
								}
								else
								{
									$html .= '&nbsp;';
								}
								break;
							}
							case 'pulldown':
							{
								if (!empty($res2['answer']))
								{
									if (is_serialized($res2['answer']))
									{
										$answers = unserialize($res2['answer']);
									}
									if (empty($answers))
									{
										$answers = array ();
									}
									$fix = '';
									foreach ($answers as $answered)
									{
										$fix .= stripslashes($answered) . ', ';
									}
									$html .= mb_substr($fix, 0, -2);
								}
								else
								{
									$html .= '&nbsp;';
								}
								break;
							}
						}
						$html .= '</fieldset></td></tr>';
					}
					else
					{
						$html = '';
					}
				}
			}
			else
			{
				$html = '';
			}
		}
		($apihook = $ilance->api('construct_register_questions_end')) ? eval($apihook) : false;
	
		return $html;
	}
    
	/**
	* Function to process submitted custom profile questions to be stored within the database
	*
	* @param       array         custom answers stored in array format
	* @param       integer       user id
	* 
	* @return      mixed         unique online account balance number
	*/
	function process_custom_profile_questions(&$custom, $userid)
	{
		global $ilance;
		if (isset($custom) AND is_array($custom))
		{
			foreach ($custom as $questionid => $answerarray)
			{
				foreach ($answerarray as $formname => $answer)
				{
					$sql = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "profile_answers
						WHERE user_id = '" . intval($userid) . "'
							AND questionid = '" . intval($questionid) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						if (is_array($answer))
						{
						    $answer = serialize($answer);
						}
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "profile_answers
							SET answer = '" . $ilance->db->escape_string($answer) . "',
							date = '" . DATETIME24H . "'
							WHERE questionid = '" . intval($questionid) . "'
								AND user_id = '" . intval($userid) . "'
						");
					}
					else
					{
						if (is_array($answer))
						{
							$answer = serialize($answer);
						}
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "profile_answers
							(answerid, questionid, user_id, answer, date, visible)
							VALUES (
							NULL,
							'" . intval($questionid) . "',
							'" . intval($userid) . "',
							'" . $ilance->db->escape_string($answer) . "',
							'" . DATETIME24H . "',
							'1')
						");
					}
				}
			}
		}
	}
    
	/**
	* Function to print inline all invited users for a particular service auction
	*
	* @param       string        profile answer
	* @param       integer       project id
	*
	* @return      nothing
	*/
	function insert_profile_answers($profile_ans, $projectid)
	{
		global $ilance, $ilconfig, $phrase;
		$answeredarray = array ();
		foreach ($profile_ans AS $type => $answerarray)
		{
			if ($type == 'range')
			{
				foreach ($answerarray AS $questionid => $answers)
				{
					if (!empty($answers) AND is_array($answers) AND $questionid > 0)
					{
						foreach ($answers AS $key => $value)
						{
							if (!empty($key) AND !empty($value) AND $value > 0)
							{
								$answeredarray[$questionid][$key] = $value;
							}
						}
					}
					if (!empty($answeredarray) AND is_array($answeredarray) AND $questionid > 0 AND !empty($answeredarray[$questionid]['from']) AND !empty($answeredarray[$questionid]['to']))
					{
						$sqlfield = $ilance->db->query("
							SELECT questionid, project_id, answer
							FROM " . DB_PREFIX . "profile_filter_auction_answers
							WHERE questionid = '" . intval($questionid) . "'
								AND project_id = '" . intval($projectid) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sqlfield) > 0)
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "profile_filter_auction_answers
								SET answer = '" . $ilance->db->escape_string($answeredarray[$questionid]['from'] . '|' . $answeredarray[$questionid]['to']) . "'
								WHERE questionid = '" . intval($questionid) . "'
								AND project_id = '" . intval($projectid) . "'
							", 0, null, __FILE__, __LINE__);
						}
						else
						{
							$ilance->db->query("
								INSERT INTO " . DB_PREFIX . "profile_filter_auction_answers
								(answerid, questionid, project_id, user_id, answer, filtertype, date, visible)
								VALUES(
								NULL,
								'" . intval($questionid) . "',
								'" . intval($projectid) . "',
								'" . $_SESSION['ilancedata']['user']['userid'] . "',
								'" . intval($answeredarray[$questionid]['from']) . '|' . intval($answeredarray[$questionid]['to']) . "',
								'range',
								'" . DATETIME24H . "',
								'1')
							", 0, null, __FILE__, __LINE__);
					    }
					}
				}
			}
			else if (mb_ereg('choice_', $type))
			{
				foreach ($answerarray AS $questionid => $answers)
				{
					if (!empty($answers) AND is_array($answers) AND $questionid > 0)
					{
						foreach ($answers AS $key => $value)
						{
							if (!empty($key) AND !empty($value))
							{
								$answeredarray[$questionid][$key] = $value;
							}
						}
						if (!empty($answeredarray) AND is_array($answeredarray) AND $questionid > 0)
						{
							$sqlfield = $ilance->db->query("
								SELECT questionid, project_id, answer
								FROM " . DB_PREFIX . "profile_filter_auction_answers
								WHERE questionid = '" . intval($questionid) . "'
									AND project_id = '" . intval($projectid) . "'
							", 0, null, __FILE__, __LINE__);
						    if ($ilance->db->num_rows($sqlfield) > 0)
						    {
								$res = $ilance->db->fetch_array($sqlfield, DB_ASSOC);
								$custom = '';
								if (!empty($res['answer']))
								{
									$currentanswers = explode('|', $res['answer']);
									if (!in_array($answeredarray[$questionid]['custom'], $currentanswers))
									{
										$custom = $res['answer'] . '|' . $answeredarray[$questionid]['custom'];
									}
									else
									{
										$custom = $res['answer'];
									}
								}
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "profile_filter_auction_answers
									SET answer = '" . $ilance->db->escape_string($custom) . "'
									WHERE questionid = '" . intval($questionid) . "'
										AND project_id = '" . intval($projectid) . "'
								", 0, null, __FILE__, __LINE__);
							}
							else
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "profile_filter_auction_answers
									(answerid, questionid, project_id, user_id, answer, filtertype, date, visible)
									VALUES(
									NULL,
									'" . intval($questionid) . "',
									'" . intval($projectid) . "',
									'" . $_SESSION['ilancedata']['user']['userid'] . "',
									'" . $ilance->db->escape_string($answeredarray[$questionid]['custom']) . "',
									'checkbox',
									'" . DATETIME24H . "',
									'1')
								", 0, null, __FILE__, __LINE__);
							}
						}
					}
				}
			}
		}
	}
    
	/**
	* Function to process profile questions which is ultimately updated or inserted as new data within the database.
	*
	* @param       array          answers (keys and values)
	* @param       integer        user id
	*
	* @return      nothing
	*/
	function process_profile_questions(&$custom, $userid = 0)
	{
		global $ilance, $ilconfig;
		if (isset($custom) AND is_array($custom))
		{
			foreach ($custom AS $key => $value)
			{
				$sql = $ilance->db->query("
					SELECT answerid
					FROM " . DB_PREFIX . "profile_answers
					WHERE questionid = '" . intval($key) . "'
						AND user_id = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
			    if ($ilance->db->num_rows($sql) > 0)
			    {
					if (isset($value))
					{
						if (is_array($value))
						{
							$value = serialize($value);
						}
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "profile_answers
							SET answer = '" . $ilance->db->escape_string($value) . "'
							WHERE questionid = '" . intval($key) . "'
								AND user_id = '" . intval($userid) . "'
						", 0, null, __FILE__, __LINE__);
					}
					else
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "profile_answers
							WHERE questionid = '" . intval($key) . "'
								AND user_id = '" . intval($userid) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
				else
				{
					if (!empty($value))
					{
						if (is_array($value))
						{
							$value = serialize($value);
						}
						$expiry = date('Y-m-d H:i:s', (TIMESTAMPNOW + $ilconfig['verificationlength'] * 24 * 3600));
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "profile_answers
							(answerid, questionid, user_id, answer, date, visible, isverified, verifyexpiry, invoiceid)
							VALUES(
							NULL,
							'" . intval($key) . "',
							'" . intval($userid) . "',
							'" . $ilance->db->escape_string($value) . "',
							'" . DATETIME24H . "',
							'1',
							'0',
							'" . $expiry . "',
							'0')
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
	}

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>