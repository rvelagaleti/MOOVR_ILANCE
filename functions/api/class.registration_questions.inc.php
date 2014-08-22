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
* Registration questions class to perform displaying and updating for registration questions within ILance.
*
* @package      iLance\Registration\Questions
* @version      4.0.0.8059
* @author       ILance
*/
class registration_questions
{
        /**
        * Function for displaying custom registration questions based on the pages within registration the admin has predefined.
        *
        * @param       integer       page number
        * @param       string        mode (input, updateprofile, updateprofileadmin, update and output1)
        * @param       integer       user id
        *
        * @return      string        HTML representation of the question registration question
        */
        function construct_register_questions($pageid = 1, $mode = '', $userid = 0, $roleid = 0)
        {
                global $ilance, $phrase, $headinclude, $ilconfig, $ilpage;
		$html = '';
                if ($mode == 'input')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "register_questions
                                WHERE pageid = '" . intval($pageid) . "'
                                    AND visible = '1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $c = 0;
                                $html = $isrequiredjs = $isrequired = '';
                                $headinclude .= "<script type=\"text/javascript\">
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
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                	$show['role'] = false;
                                	$roles = explode('|', $res['roleid']);
                                	if (is_array($roles))
                                	{
	                                	foreach ($roles AS $key => $value)
	                                	{
	                                		if ($value == $roleid)
	                                		{
	                                			$show['role'] = true;
	                                		}
	                                	}
                                	}
                                	else 
                                	{
                                		if ($res['roleid'] == $roleid)
                                		{
                                			$show['role'] = true;
                                		}
                                	}
                                	if ($show['role'])
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
								$input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked"> {_yes}</label> <label for="' . $res['formname'] . '0"><input type="radio" id="' . $res['formname'] . '0" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0"> {_no}</label>';
								$overridejs = 1;
								break;
							}                        
							case 'int':
							{
								$input = '<input class="input" size="3" type="text" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" style="font-family: verdana" />';
								break;
							}
							case 'textarea':
							{
								//$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $formdefault . '</textarea><br /> <div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">'.'{_increase_size}'.'</a>&nbsp; <a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">'.'{_decrease_size}'.'</a></div></div>';
								$input = '<style id="wysiwyg_html" type="text/css">
<!--
' . $ilance->styles->css_cache['csswysiwyg'] . '
//-->
</style>
<div class="ilance_wysiwyg">
<table cellpadding="0" cellspacing="0" border="0" width="440" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
<td class="wysiwyg_wrapper" align="right" height="25">

	<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
	<tr>
		<td width="100%" align="left" class="smaller">{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}</td>
		<td>
			<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="{_decrease_size}" border="0" /></a></div>
			<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="{_increase_size}" border="0" /></a></div>
		</td>
		<td style="padding-right:15px"></td>
	</tr>
	</table>
</td>
</tr>
	<tr>
		<td><textarea id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:440px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $formdefault . '</textarea></td>
	</tr>
</table>
</div>';
								break;
							}                        
							case 'text':
							{
								$input = '<input class="input" type="text" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" style="font-family: verdana" />';
								break;
							}                                                
							case 'multiplechoice':
							{
								if (!empty($res['multiplechoice']))
								{
									$choices = explode('|', $res['multiplechoice']);
									$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']">';
									$input .= '<option value="">-</option>';
									$input .= '<optgroup name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" label="{_select}" >';
									foreach ($choices as $choice)
									{
										if (!empty($choice))
										{
											$default = ($choice == $res['formdefault']) ? ' selected' : '';
											$input .= '<option value="' . trim(ilance_htmlentities($choice)) . '"'. $default . '>' . trim(ilance_htmlentities($choice)) . '</option>';
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
									$input = '<select name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="font-family: verdana">';
									foreach ($choices as $choice)
									{
										if (!empty($choice))
										{
											$default = ($choice == $res['formdefault']) ? ' selected' : '';
											$input .= '<option value="' . trim(ilance_htmlentities($choice)) . '"'. $default . '>' . trim(ilance_htmlentities($choice)) . '</option>';
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
							$formname = $res['formname'];
							if(isset($_POST['custom'][$questionid][$formname]))
							{
								if ((is_array($_POST['custom'][$questionid][$formname]) AND empty($_POST['custom'][$questionid][$formname]['0']) AND $_POST['custom'][$questionid][$formname]['0'] != '0') OR (empty($_POST['custom'][$questionid][$formname]) AND $_POST['custom'][$questionid][$formname] != '0'))
								{
										$isrequired = '<img name="custom[' . $res["questionid"] . '][' . $res["formname"] . ']error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif" width="21" height="13" border="0" alt="{_this_form_field_is_required}" />';
								}
								else 
								{
									$isrequired = '<img name="custom[' . $res["questionid"] . '][' . $res["formname"] . ']error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="{_this_form_field_is_required}" />';
								}
							}
						}
						else
						{
							$isrequired = '';
						}
						$html .= '<table width="100%"  border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong></div></td>
</tr>
<tr>
	<td><div class="gray" style="padding-bottom:3px">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div>' . $input . ' ' . $isrequired . '</td>
</tr>
</table>
<div style="padding-bottom:9px"></div>';
						$c++;
						}
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
				FROM " . DB_PREFIX . "register_questions
				WHERE visible = '1'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$c = 0;
				$isrequiredjs = $isrequired = '';
				$html .= '<table cellpadding="5" cellspacing="0" border="0" dir="' . $ilconfig['template_textdirection'] . '">';
				$headinclude .= "<script type=\"text/javascript\">
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
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                            		$role = false;
                            		$roles = explode('|', $res['roleid']);
                            		foreach($roles AS $key => $value)
                            		{
                            			if ($value == $roleid)
                            			{
                            				$role = true;
                            			}
                            		}
                                	if ($role)
                                	{	
						// do we have an answer?
						$answertoinput = $formdefault = '';
						$sql2 = $ilance->db->query("
							SELECT answer
							FROM " . DB_PREFIX . "register_answers
							WHERE questionid = '" . $res['questionid'] . "'
							    AND user_id = '" . intval($userid) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0)
						{
							$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
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
										$input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> {_yes} </label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> {_no} </label>';
									}
									else
									{
										$input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" /> {_yes} </label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" checked="checked" /> {_no} </label>';
									}
								}
								else
								{
									$input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> {_yes} </label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> {_no} </label>';
								}
								break;
							}                                            
							case 'int':
							{
								$input = '<input class="input" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
								break;
							}                                            
							case 'textarea':
							{
								//$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $answertoinput . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">'.'{_increase_size}'.'</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">'.'{_decrease_size}'.'</a></div></div>';
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
		<td width="100%" align="left" class="smaller">{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}</td>
		<td>
			<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="{_decrease_size}" border="0" /></a></div>
			<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="{_increase_size}" border="0" /></a></div>
		</td>
		<td style="padding-right:15px"></td>
	</tr>
	</table>
</td>
</tr>
	<tr>
		<td><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea></td>
	</tr>
</table>
</div>';
								break;
							}                                            
							case 'text':
							{
								$input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" size="50" />';
								break;
							}                                            
							case 'multiplechoice':
							{
								if (!empty($res['multiplechoice']))
								{
									$choices = explode('|', $res['multiplechoice']);
									$input = '<select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
									if (is_serialized($answertoinput))
									{
										$answers = unserialize($answertoinput);
									}
									if (empty($answers))
									{
										$answers = array();
									}
									$input .= '<optgroup label="{_select}">';
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
									$input = '<select name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '" style="font-family: verdana">';
									if (is_serialized($answertoinput))
									{
										$answers = unserialize($answertoinput);
									}
									if (empty($answers))
									{
										$answers = array();
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
						if ($res['required'])
						{
							$isrequired = '<span title="{_this_form_field_is_required}"><img name="' . $res['formname'] . 'error" src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="" /></span>';
							$isrequiredjs .= "\n (fetch_js_object('" . $res['formname'] . "').value.length < 1) ? customImage(\"" . $res['formname'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . $res['formname'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
						}
						else
						{
							$isrequired = '';
						}
						/*$html .= '<div style="padding-bottom:3px"><div>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td colspan="3" nowrap="nowrap"><div class="gray">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div></td>
</tr>
<tr>
	<td align="left" height="33">' . $input . ' ' . $isrequired . '</td>
</tr>
</table><div style="padding-bottom:9px"></div></div>';*/
						$html .= '<tr> 
      <td align="right"><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . ':</strong></td>
      <td>' . $input . ' ' . $isrequired . '<div class="smaller gray" style="padding-top:3px">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div></td>
</tr>';
						$c++;
					}
				}
				$html .= '</table>';
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
                                FROM " . DB_PREFIX . "register_questions
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
                                                FROM " . DB_PREFIX . "register_answers
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
                                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> {_yes}&nbsp;&nbsp;</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> {_no}</label>';
                                                                        }
                                                                        else
                                                                        {
                                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" /> {_yes}&nbsp;&nbsp;</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" checked="checked" /> {_no}</label>';
                                                                        }
                                                                }
                                                                else
                                                                {
                                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> {_yes}&nbsp;&nbsp;</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> {_no}</label>';
                                                                }
                                                                break;
                                                        }                                                    
                                                        case 'int':
                                                        {
                                                                $input = '<input class="input" size="3" type="text" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                                break;
                                                        }                                                    
                                                        case 'textarea':
                                                        {
                                                                $input = '<textarea id="' . $res['formname'] . '" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:250px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea>';
                                                                break;
                                                        }                                                    
                                                        case 'text':
                                                        {
                                                                $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom1[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                                break;
                                                        }                                                    
                                                        case 'multiplechoice':
                                                        {
                                                                if (!empty($res['multiplechoice']))
                                                                {
                                                                        $choices = explode('|', $res['multiplechoice']);
                                                                        $input = '<select style="width:250px; height:70px; font-family: verdana" multiple name="custom1[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                        $answers = array();
                                                                        if (is_serialized($answertoinput))
                                                                        {
                                                                                $answers = unserialize($answertoinput);
                                                                        }
                                                                        if (empty($answers))
                                                                        {
                                                                                $answers = array();
                                                                        }
                                                                        foreach ($choices AS $choice)
                                                                        {
                                                                                if (in_array($choice, $answers))
                                                                                {
                                                                                        $input .= '<option value="' . handle_input_keywords(trim($choice)) . '" selected="selected">' . handle_input_keywords($choice) . '</option>';
                                                                                }
                                                                                else
                                                                                {
                                                                                        $input .= '<option value="' . handle_input_keywords(trim($choice)) . '">' . handle_input_keywords($choice) . '</option>';
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
                                                                        $input = '<select name="custom1[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '" style="font-family: verdana">';
                                                                        
                                                                        if (is_serialized($answertoinput))
                                                                        {
                                                                                $answers = unserialize($answertoinput);
                                                                        }
                                                                        if (empty($answers))
                                                                        {
                                                                                $answers = array();
                                                                        }
                                                                        foreach ($choices AS $choice)
                                                                        {
                                                                                if (in_array($choice, $answers))
                                                                                {
                                                                                        $input .= '<option value="' . handle_input_keywords(trim($choice)) . '" selected="selected">' . handle_input_keywords($choice) . '</option>';
                                                                                }
                                                                                else
                                                                                {
                                                                                        $input .= '<option value="' . handle_input_keywords(trim($choice)) . '">' . handle_input_keywords($choice) . '</option>';
                                                                                }
                                                                        }
                                                                        $input .= '</select>';
                                                                }
                                                                break;
                                                        }
                                                }
                            
                                                $html .= '<tr class="alt1" valign="top">
							<td><span class="gray">' . handle_input_keywords(stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']])) . '</span></td>
							<td><div><span style="float:right;padding-left:6px" class="smaller blueonly"><a href="' . $ilpage['subscribers'] . '?subcmd=_remove-registration-answer&amp;id=' . $res2['answerid'] . '&amp;uid=' . intval($userid) . '" target="_self" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">{_remove_this_answer}</a></span>' . $input . '</div></td>
						</tr>';
                                                $c++;
                                        }
                                }
                        }
                }
                else if ($mode == 'update')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "register_questions
                                WHERE pageid = '" . intval($pageid) . "'
                                    AND visible = '1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $c = 0;
                                $html = $isrequiredjs = '';
                
                                // enable custom header javascript
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
                                        // do we have an answer?
                                        $answertoinput = '';
                                        
                                        $sql2 = $ilance->db->query("
                                                SELECT answer
                                                FROM " . DB_PREFIX . "register_answers
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
                                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> {_yes}</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> {_no}</label>';
                                                                }
                                                                else
                                                                {
                                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" /> {_yes}</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" checked="checked" /> {_no}</label>';
                                                                }
                                                        }
                                                        else
                                                        {
                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> {_yes}</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> {_no}</label>';
                                                        }
                                                        break;
                                                }                        
                                                case 'int':
                                                {
                                                        $input = '<input class="input" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                        break;
                                                }                        
                                                case 'textarea':
                                                {
                                                        //$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $answertoinput . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">'.'{_increase_size}'.'</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">'.'{_decrease_size}'.'</a></div></div>';
                                                        $input = '
                                                        <style id="wysiwyg_html" type="text/css">
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
                                                                        <td width="100%" align="left" class="smaller">{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}</td>
                                                                        <td>
                                                                                        <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="{_decrease_size}" border="0" /></a></div>
                                                                                        <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="{_increase_size}" border="0" /></a></div>
                                                                        </td>
                                                                        <td style="padding-right:15px"></td>
                                                                </tr>
                                                                </table>
                                                        </td>
                                                        </tr>
                                                                <tr>
                                                                        <td><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea></td>
                                                                </tr>
                                                        </table>
                                                        </div>';
                                                        
                                                        break;
                                                }                        
                                                case 'text':
                                                {
                                                        $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                        break;
                                                }                                            
                                                case 'multiplechoice':
                                                {
                                                        if (!empty($res['multiplechoice']))
                                                        {
                                                                $choices = explode('|', $res['multiplechoice']);
                                                                $input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                
                                                                if (is_serialized($answertoinput))
                                                                {
                                                                        $answers = unserialize($answertoinput);
                                                                }
                                                                
                                                                if (empty($answers))
                                                                {
                                                                        $answers = array();
                                                                }
                                                                
                                                                $input .= '<optgroup label="{_select}">';
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
                                                                $input = '<select name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '" style="font-family: verdana">';
                                                                
                                                                if (is_serialized($answertoinput))
                                                                {
                                                                        $answers = unserialize($answertoinput);
                                                                }
                                                                
                                                                if (empty($answers))
                                                                {
                                                                        $answers = array();
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
                                                $isrequired = '<img name="' . $res['formname'] . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="{_this_form_field_is_required}" />';
                                                $isrequiredjs .= "\n(fetch_js_object('" . $res['formname'] . "').value.length < 1) ? customImage(\"" . $res['formname'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . $res['formname'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
                                        }
                                        else
                                        {
                                                $isrequired = '';
                                        }
                    
                                        $html .= '
                                        <tr>
                                            <td colspan="5">
                                                    <table width="100%" border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
                                                    <tr>
                                                            <td width="50%" valign="top">
                    
                                                                    <fieldset class="fieldset" style="margin:0px">
                                                                    <legend>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</legend>
                                                                    <table width="100%" border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
                                                                    <tr>
                                                                            <td colspan="3">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . ' ' . $isrequired . '</td>
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
                                FROM " . DB_PREFIX . "register_questions
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
                                                FROM " . DB_PREFIX . "register_answers
                                                WHERE questionid = '" . $res['questionid'] . "'
                                                    AND user_id = '" . intval($userid) . "'
                                                    AND visible = '1'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2);
                                                $html .= '<tr><td colspan="4" align="left"><fieldset class="fieldset" style="margin:0px"><legend>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</legend>';
                        
                                                // input type switch display
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
                                                                                $answers = array();
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
                                                                                $answers = array();
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
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>