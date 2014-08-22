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

if (!class_exists('bid'))
{
	exit;
}

/**
* Class to handle the auction posting interface for any type of auction supported in ILance.
*
* @package      iLance\Bid\Fields
* @version      4.0.0.8059
* @author       ILance
*/
class bid_fields extends bid
{
        /**
        * Function to handle all answerable auction questions within the posting system.
        *
        * @param       integer       category id
        * @param       integer       project id
        * @param       string        display mode (input, preview, update, output1)
        * @param       string        category type (service or product)
        * @param       integer       bid id
        * @param       boolean       force separator (<hr> tag)? (default true)
        *
        * @return      string        HTML representation of the custom auction questions
        */
        function construct_bid_fields($cid = 0, $projectid = 0, $mode = '', $type = '', $bidid = 0, $separator = true)
        {
                global $ilance, $ilpage, $phrase, $headinclude, $ilconfig, $show;
                if ($type == 'service')
                {
                        $table1 = 'bid_fields';
                        $table2 = 'bid_fields_answers';
                }
                else
                {
                        return false;
                }
                $cols = 0;
                $columns = 3;
                $width = number_format((100 / $columns));
                $html = '<table width="100%" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" dir="' . $ilconfig['template_textdirection'] . '">';
                // #### QUESTION DISPLAY TYPE ##################################
                if ($mode == 'input')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table1 . "
                                        WHERE visible = '1'
                                        ORDER BY sort
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $show['bidfieldstable'] = true;
                                $questions = $ilance->db->num_rows($sql);
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
                                $isrequiredjs = '';
                                $num = 0;                                
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        if ($this->can_display_bid_field($res['fieldid'], $cid))
                                        {
                                                $res['formname'] = $res['fieldid'];
                                                $res['questionid'] = $res['fieldid'];
                                                $formdefault = '';
                                                if (isset($res['formdefault']) AND $res['formdefault'] != '')
                                                {
                                                        $formdefault = $res['formdefault'];
                                                }
                                                $overridejs = 0;
                                                switch ($res['inputtype'])
                                                {
                                                        case 'yesno':
                                                        {
                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked"> ' . '{_yes}' . '</label> <label for="' . $res['formname'] . '0"><input type="radio" id="' . $res['formname'] . '0" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0"> ' . '{_no}' . '</label>';
                                                                $overridejs = 1;
                                                                break;
                                                        }                                
                                                        case 'int':
                                                        {
                                                                $input = '<input class="input" size="3" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" />';
                                                                break;
                                                        }
                                                        case 'textarea':
                                                        {
                                                                $input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:200px; height:84px; padding:8px;" wrap="physical">' . $formdefault . '</textarea><br /><div style="width:180px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">' . '{_increase_size}' . '</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">' . '{_decrease_size}' . '</a></div></div>';
                                                                break;
                                                        }                                
                                                        case 'text':
                                                        {
                                                                $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" />';
                                                                break;
                                                        }
                                                        case 'url':
                                                        {
                                                                $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" />';
                                                                break;
                                                        }                                                        
                                                        case 'multiplechoice':
                                                        {
                                                                if (!empty($res['multiplechoice']))
                                                                {
                                                                        $choices = explode('|', $res['multiplechoice']);
                                                                        $input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}' . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                        $input .= '<option value="">-</option>';
                                                                        $input .= '<optgroup label="' . '{_select}' . ':">';
                                                                        foreach ($choices AS $choice)
                                                                        {
                                                                                if (!empty($choice))
                                                                                {
                                                                                        //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                        $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '">' . trim(ilance_htmlentities($choice)) . '</option>';
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
                                                                        $input = '<select style="font-family: verdana" name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                        $input .= '<option value="">-</option>';
                                                                        foreach ($choices AS $choice)
                                                                        {
                                                                                if (!empty($choice))
                                                                                {
                                                                                        //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                        $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                                }
                                                                        }
                                                                        $input .= '</select>';
                                                                }
                                                                break;
                                                        }
                                                        case 'date':
                                                        {
                                                        	$input = '<input class="input" type="date" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" />';
                                                                break;        
                                                        }
                                                }
                                                $isrequired = '';
                                                if ($res['required'] AND $overridejs == 0)
                                                {
                                                        $isrequired .= '<img name="' . stripslashes($res['formname']) . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . '{_this_form_field_is_required}' . '" />';
                                                        $isrequiredjs .= "\n(fetch_js_object('" . stripslashes($res['formname']) . "').value.length < 1) ? customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
                                                }
                                                if ($cols == 0)
                                                {
                                                       $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                                }
                                                $html .= '<td width="' . $width . '%" valign="top"><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong><div class="gray">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div><div>' . $input . ' ' . $isrequired . '</div></div><div style="padding-bottom:7px"></div></td>';
                                                $cols++;
                                                $num++;
                                                $c++;
                                                if ($cols == $columns)
                                                {
                                                        $html .= '</tr>';
                                                        $cols = 0;
                                                }        
                                        }
                                }
                                if ($cols != $columns && $cols != 0)
                                {
                                        $neededtds = $columns - $cols;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                $html .= '<td></td>';
                                        }
                                        
                                        $html .= '</tr>'; 
                                }
                                $headinclude .= $isrequiredjs;
                                $headinclude .= "\nreturn (!haveerrors);\n";
                                $headinclude .= "}\n";
                                $headinclude .= "</script>\n";
                        }
                        else
                        {
                                $show['categoryfindertable'] = false;
                                $html = '';
                                $headinclude .= "<script type=\"text/javascript\">function validatecustomform(f) { return true; }</script>\n";
                        }
                }
                else if ($mode == 'preview')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table1 . "
                                WHERE visible = '1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $show['bidfieldstable'] = true;
                                $questions = $ilance->db->num_rows($sql);
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
                                $num = 0;
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        if ($this->can_display_bid_field($res['fieldid'], $cid))
                                        {
                                                $res['formname'] = $res['fieldid'];
                                                $res['questionid'] = $res['fieldid'];
                                                $formdefault = '';
                                                if (isset($res['formdefault']) AND $res['formdefault'] != '')
                                                {
                                                        $formdefault = $res['formdefault'];
                                                }
                                                $overridejs = 0;
                                                switch ($res['inputtype'])
                                                {
                                                        case 'yesno':
                                                        {
                                                                if (is_array($ilance->GPC['custom']))
                                                                {
                                                                        foreach ($ilance->GPC['custom'] as $questionid => $answerarray)
                                                                        {
                                                                                if ($res['questionid'] == $questionid)
                                                                                {
                                                                                        foreach ($answerarray as $formname => $answer)
                                                                                        {
                                                                                                if (isset($answer) AND $answer == 1)
                                                                                                {
                                                                                                        $checked1 = 'checked="checked"';
                                                                                                        $checked2 = '';
                                                                                                }
                                                                                                else 
                                                                                                {
                                                                                                        $checked1 = '';
                                                                                                        $checked2 = 'checked="checked"';
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                                else 
                                                                {
                                                                        $checked1 = 'checked="checked"';
                                                                        $checked2 = '';	
                                                                }
                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" ' . $checked1 . ' /> ' . '{_yes}' . '</label> <label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" ' . $checked2 . ' /> ' . '{_no}' . '</label>';
                                                                $overridejs = 1;
                                                                break;
                                                        }                                
                                                        case 'int':
                                                        {
                                                                if (is_array($ilance->GPC['custom']))
                                                                {
                                                                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                                                                        {
                                                                                if ($res['questionid'] == $questionid)
                                                                                {
                                                                                        foreach ($answerarray AS $formname => $answer)
                                                                                        {
                                                                                                $value = '';
                                                                                                if (isset($answer) AND !empty($answer))
                                                                                                {
                                                                                                        //$value = stripslashes(strip_tags(htmlentities($answer, ENT_QUOTES)));
                                                                                                        $value = stripslashes(strip_tags(ilance_htmlentities($answer)));
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                                $input = '<input id="' . $res['formname'] . '" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $value . '" class="input" />';
                                                                break;
                                                        }
                                                        case 'textarea':
                                                        {
                                                                if (is_array($ilance->GPC['custom']))
                                                                {
                                                                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                                                                        {
                                                                                if ($res['questionid'] == $questionid)
                                                                                {
                                                                                        foreach ($answerarray AS $formname => $answer)
                                                                                        {
                                                                                                $value = '';
                                                                                                if (isset($answer) AND !empty($answer))
                                                                                                {
                                                                                                        //$value = stripslashes(strip_tags(htmlentities($answer, ENT_QUOTES)));
                                                                                                        $value = stripslashes(strip_tags(ilance_htmlentities($answer)));
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                                $input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:200px; height:84px; padding:8px; font-family: verdana" wrap="physical">' . $value . '</textarea><br /><div style="width:180px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">' . '{_increase_size}' . '</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">' . '{_decrease_size}' . '</a></div></div>';
                                                                break;
                                                        }
                                                        case 'text':
                                                        {
                                                                if (is_array($ilance->GPC['custom']))
                                                                {
                                                                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                                                                        {
                                                                                if ($res['questionid'] == $questionid)
                                                                                {
                                                                                        foreach ($answerarray AS $formname => $answer)
                                                                                        {
                                                                                                $value = '';
                                                                                                if (isset($answer) AND !empty($answer))
                                                                                                {
                                                                                                        //$value = stripslashes(strip_tags(htmlentities($answer, ENT_QUOTES)));
                                                                                                        $value = stripslashes(strip_tags(ilance_htmlentities($answer)));
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                                $input = '<input type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $value . '" class="input" />';
                                                                break;
                                                        }
                                                        case 'multiplechoice':
                                                        {
                                                                if (!empty($res['multiplechoice']))
                                                                {
                                                                        $choices = explode('|', $res['multiplechoice']);
                                                                        $input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}' . '<br />';
                                                                        $input .= '<select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                        $input .= '<optgroup label="' . '{_select}' . ':">';
                                                                        $input .= '<option value="">-</option>';
                                                                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                                                                        {
                                                                                if ($res['questionid'] == $questionid)
                                                                                {
                                                                                        foreach ($answerarray AS $formname => $answer)
                                                                                        {
                                                                                                foreach ($answer AS $choicevalue)
                                                                                                {
                                                                                                        //$selected[] = trim(htmlentities($choicevalue, ENT_QUOTES));
                                                                                                        $selected[] = trim(ilance_htmlentities($choicevalue));
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }
                                                                        foreach ($choices AS $choice)
                                                                        {
                                                                                if (!empty($choice))
                                                                                {
                                                                                        //$choice = trim(htmlentities($choice, ENT_QUOTES));
                                                                                        $choice = trim(ilance_htmlentities($choice));
                                                                                        if (!empty($selected) AND in_array($choice, $selected))
                                                                                        {
                                                                                                $input .= '<option value="' . $choice . '" selected="selected">' . $choice . '</option>';	
                                                                                        }
                                                                                        else 
                                                                                        {
                                                                                                $input .= '<option value="' . $choice . '">' . $choice . '</option>';
                                                                                        }    
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
                                                                        $input =  '<select style="font-family: verdana" name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                        $input .= '<option value="">-</option>';
                                                                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                                                                        {
                                                                                if ($res['questionid'] == $questionid)
                                                                                {
                                                                                        foreach ($answerarray AS $formname => $answer)
                                                                                        {
                                                                                                foreach ($answer AS $choicevalue)
                                                                                                {
                                                                                                        //$selected[] = trim(htmlentities($choicevalue, ENT_QUOTES));
                                                                                                        $selected[] = trim(ilance_htmlentities($choicevalue));
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }
                                                                        foreach ($choices AS $choice)
                                                                        {
                                                                                if (!empty($choice))
                                                                                {
                                                                                        //$choice = trim(htmlentities($choice, ENT_QUOTES));
                                                                                        $choice = trim(ilance_htmlentities($choice));
                                                                                        if (!empty($selected) AND in_array($choice, $selected))
                                                                                        {
                                                                                                $input .= '<option value="' . $choice . '" selected="selected">' . $choice . '</option>';
                                                                                        }
                                                                                        else 
                                                                                        {
                                                                                                $input .= '<option value="' . $choice . '">' . $choice . '</option>';
                                                                                        }    
                                                                                }
                                                                        }
                                                                        $input .= '</select>';
                                                                        unset($selected);
                                                                }
                                                                break;
                                                        }
                                                        case 'date':
                                                        {
                                                        	if (is_array($ilance->GPC['custom']))
                                                                {
                                                                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                                                                        {
                                                                                if ($res['questionid'] == $questionid)
                                                                                {
                                                                                        foreach ($answerarray AS $formname => $answer)
                                                                                        {
                                                                                                $value = '';
                                                                                                if (isset($answer) AND !empty($answer))
                                                                                                {
                                                                                                        //$value = stripslashes(strip_tags(htmlentities($answer, ENT_QUOTES)));
                                                                                                        $value = stripslashes(strip_tags(ilance_htmlentities($answer)));
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                                $input = '<input type="date" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $value . '" class="input" />';
                                                                break;       
                                                        }
                                                }
                                                $isrequired = '';
                                                if ($res['required'] AND $overridejs == 0)
                                                {
                                                        $isrequired .= '<img name="' . stripslashes($res['formname']) . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . '{_this_form_field_is_required}' . '" />';
                                                        $isrequiredjs .= "\n(fetch_js_object('" . stripslashes($res['formname']) . "').value.length < 1) ? customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
                                                }
                                                if ($cols == 0)
                                                {
                                                       $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                                }
                                                $html .= '<td width="' . $width . '%"><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong><div class="gray">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div><div>' . $input . ' ' . $isrequired . '</div></div><div style="padding-bottom:7px"></div></td>';
                                                $cols++;
                                                $num++;
                                                $c++;
                                                if ($cols == $columns)
                                                {
                                                        $html .= '</tr>';
                                                        $cols = 0;
                                                }
                                        }    
                                }
                                if ($cols != $columns && $cols != 0)
                                {
                                        $neededtds = $columns - $cols;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                $html .= '<td></td>';
                                        }
                                        $html .= '</tr>'; 
                                }
                                $headinclude .= $isrequiredjs;
                                $headinclude .= "\nreturn (!haveerrors);\n";
                                $headinclude .= "}\n";
                                $headinclude .= "</script>\n";
                        }
                        else
                        {
                                $show['bidfieldstable'] = false;
                                $headinclude .= "<script type=\"text/javascript\">function validatecustomform(f) { return true; }</script>\n";
                        }
                }
                else if ($mode == 'update')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table1 . "
                                WHERE visible = '1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $show['bidfieldstable'] = true;
                                $questions = $ilance->db->num_rows($sql);
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
                                $num = 0;
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        if ($this->can_display_bid_field($res['fieldid'], $cid))
                                        {
                                                $res['formname'] = $res['fieldid'];
                                                $res['questionid'] = $res['fieldid'];
                                                $answertoinput = '';
                                                $sql2 = $ilance->db->query("
                                                        SELECT answer
                                                        FROM " . DB_PREFIX . $table2 . "
                                                        WHERE fieldid = '" . $res['questionid'] . "'
                                                                AND project_id = '" . intval($projectid) . "'
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
                                                $overridejs = 0;
                                                switch ($res['inputtype'])
                                                {
                                                        case 'yesno':
                                                        {
                                                                if (!empty($answertoinput))
                                                                {
                                                                        if ($answertoinput == 1)
                                                                        {
                                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> ' . '{_yes}' . '</label><label for="' . $res['formname'] . '0"><input type="radio" id="' . $res['formname'] . '0" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> ' . '{_no}' . '</label>';
                                                                        }
                                                                        else
                                                                        {
                                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" /> ' . '{_yes}' . '</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" checked="checked" /> ' . '{_no}' . '</label>';
                                                                        }
                                                                }
                                                                else
                                                                {
                                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> ' . '{_yes}' . '</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> ' . '{_no}' . '</label>';
                                                                }
                                                                $overridejs = 1;
                                                                break;
                                                        }                                
                                                        case 'int':
                                                        {
                                                                //$input = '<input class="input" id="' . $res['formname'] . '" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . htmlentities($answertoinput, ENT_QUOTES) . '" />';
                                                                $input = '<input class="input" id="' . $res['formname'] . '" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . ilance_htmlentities($answertoinput) . '" />';
                                                                break;
                                                        }
                                                        case 'textarea':
                                                        {
                                                                //$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . htmlentities($answertoinput, ENT_QUOTES) . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">' . '{_increase_size}' . '</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">' . '{_decrease_size}' . '</a></div></div>';
                                                                $input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . ilance_htmlentities($answertoinput) . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">' . '{_increase_size}' . '</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">' . '{_decrease_size}' . '</a></div></div>';
                                                                break;
                                                        }                                
                                                        case 'text':
                                                        {
                                                                //$input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . htmlentities($answertoinput, ENT_QUOTES) . '" />';
                                                                $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . ilance_htmlentities($answertoinput) . '" />';
                                                                break;
                                                        }
                                                        case 'url':
                                                        {
                                                                //$input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . htmlentities($answertoinput, ENT_QUOTES) . '" />';
                                                                $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . ilance_htmlentities($answertoinput) . '" />';
                                                                break;
                                                        }                                                
                                                        case 'multiplechoice':
                                                        {
                                                                if (!empty($res['multiplechoice']))
                                                                {
                                                                        $choices = explode('|', $res['multiplechoice']);
                                                                        
                                                                        $input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}' . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                        $input .= '<optgroup label="' . '{_select}' . ':">';
                                                                        
                                                                        if (empty($answertoinput))
                                                                        {
                                                                                $answers = array();
                                                                        }
                                                                        else
                                                                        {
                                                                                $answers = unserialize($answertoinput);
                                                                        }
                                                                        
                                                                        foreach ($choices AS $choice)
                                                                        {
                                                                                if (in_array($choice, $answers))
                                                                                {
                                                                                        //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '" selected="selected">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                        $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '" selected="selected">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                                }
                                                                                else
                                                                                {
                                                                                        //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                        $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '">' . trim(ilance_htmlentities($choice)) . '</option>';
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
                                                                        $input = '<select style="font-family: verdana" name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="'.$res['formname'].'">';
                                                                        if (empty($answertoinput))
                                                                        {
                                                                                $answers = array();
                                                                        }
                                                                        else
                                                                        {
                                                                                $answers = unserialize($answertoinput);
                                                                        }
                                                                        $input .= '<option value="">-</option>';
                                                                        foreach ($choices AS $choice)
                                                                        {
                                                                                if (isset($answers[0]) AND $choice == $answers[0])
                                                                                {
                                                                                        //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '" selected="selected">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                        $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '" selected="selected">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                                }
                                                                                else
                                                                                {
                                                                                        //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                        $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                                }
                                                                        }
                                                                        $input .= '</select>';
                                                                }
                                                                break;
                                                        }
                                                        case 'date':
                                                        {
                                                        		$input = '<input class="input" type="date" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . ilance_htmlentities($answertoinput) . '" />';
                                                                break;        
                                                        }
                                                }
                                                if ($res['required'] AND $overridejs == 0)
                                                {
                                                        $isrequired .= '<img name="' . stripslashes($res['formname']) . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . '{_this_form_field_is_required}' . '" />';
                                                        $isrequiredjs .= "\n(fetch_js_object('" . stripslashes($res['formname']) . "').value.length < 1) ? customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
                                                }
                                                else
                                                {
                                                        $isrequired = '';
                                                }
                                                if ($cols == 0)
                                                {
                                                       $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                                }
                                                $html .= '<td width="' . $width . '%"><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong><div class="gray">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div><div>' . $input . ' ' . $isrequired . '</div></div><div style="padding-bottom:7px"></div></td>';
                                                $cols++;
                                                $num++;
                                                $c++;
                                                if ($cols == $columns)
                                                {
                                                        $html .= '</tr>';
                                                        $cols = 0;
                                                }
                                        }
                                }
                                if ($cols != $columns && $cols != 0)
                                {
                                        $neededtds = $columns - $cols;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                $html .= '<td></td>';
                                        }
                                        $html .= '</tr>'; 
                                }
                                $headinclude .= $isrequiredjs;
                                $headinclude .= "\nreturn (!haveerrors);\n";
                                $headinclude .= "}\n";
                                $headinclude .= "</script>\n";
                        }
                        else
                        {
                                $show['bidfieldstable'] = false;
                                $headinclude .= "<script type=\"text/javascript\">function validatecustomform(f) { return true; }</script>\n";
                        }
                }
                else if ($mode == 'output1')
                {
                        $show['bidspecifics'] = false;
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table1 . "
                                WHERE visible = '1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $questions = $ilance->db->num_rows($sql);
                                $c = $num = 0;
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        $answer = $htmlanswer = '';
                                        $sql2 = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . $table2 . "
                                                WHERE fieldid = '" . $res['fieldid'] . "'
                                                        AND project_id = '" . intval($projectid) . "'
                                                        AND bid_id = '" . intval($bidid) . "'
                                                        AND visible = '1'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2);
                                                $answer = $res2['answer'];
                                        }
                                        // if answer is empty don't show it
                                        if (isset($answer) AND (!empty($answer) OR $answer != ''))
                                        {
                                                $show['bidspecifics'] = true;
                                                // input type switch display
                                                switch ($res['inputtype'])
                                                {
                                                        case 'yesno':
                                                        {
                                                                if ($answer == 1)
                                                                {
                                                                        $htmlanswer .= '{_yes}';
                                                                }
                                                                else
                                                                {
                                                                        $htmlanswer .= '{_no}';
                                                                }
                                                                break;
                                                        }
                                                        case 'int':
                                                        {
                                                                $htmlanswer .= $answer . '&nbsp;';
                                                                break;
                                                        }                                        
                                                        case 'textarea':
                                                        {
                                                                //$htmlanswer .= htmlentities(stripslashes($answer), ENT_QUOTES) . '&nbsp;';
                                                                $htmlanswer .= ilance_htmlentities(stripslashes($answer)) . '&nbsp;';
                                                                break;
                                                        }                                        
                                                        case 'text':
                                                        {
                                                                //$htmlanswer .= htmlentities(stripslashes($answer), ENT_QUOTES) . '&nbsp;';
                                                                $htmlanswer .= ilance_htmlentities(stripslashes($answer)) . '&nbsp;';
                                                                break;
                                                        }
                                                        case 'multiplechoice':
                                                        {
                                                                if (!empty($answer) OR $answer != '')
                                                                {
                                                                        $answers = unserialize($answer);
                                                                        $fix = '';
                                                                        foreach ($answers AS $answered)
                                                                        {
                                                                                //$fix .= htmlentities(stripslashes($answered), ENT_QUOTES) . ', ';
                                                                                $fix .= ilance_htmlentities(stripslashes($answered)) . ', ';
                                                                        }
                                                                        $htmlanswer .= mb_substr($fix, 0, -2);
                                                                }
                                                                else
                                                                {
                                                                        $htmlanswer .= '&nbsp;';
                                                                }
                                                                break;
                                                        }
                                                        case 'pulldown':
                                                        {
                                                                if (!empty($answer) OR $answer != '')
                                                                {
                                                                        $answers = unserialize($answer);
                                                                        $fix = '';
                                                                        foreach ($answers AS $answered)
                                                                        {
                                                                                //$fix .= htmlentities(stripslashes($answered), ENT_QUOTES);
                                                                                $fix .= ilance_htmlentities(stripslashes($answered));
                                                                        }
                                                                        if (empty($fix))
                                                                        {
                                                                                $htmlanswer .= ($show['is_owner'] ? '<span style="float:right" class="smaller">[ <a href="' . $ilpage['selling'] . '?cmd=product-management&amp;state=' . $type . '&amp;id=' . intval($ilance->GPC['id']) . '#categoryfinder">' . '{_edit}' . '</a> ]</span>-' : '-');
                                                                        }
                                                                        else
                                                                        {
                                                                                $htmlanswer .= $fix;
                                                                        }
                                                                }
                                                                else
                                                                {
                                                                        $htmlanswer .= '&nbsp;';
                                                                }
                                                                break;
                                                        }
                                                        case 'date':
                                                        {
                                                        		$htmlanswer .= ilance_htmlentities(stripslashes($answer)) . '&nbsp;';
                                                               	break; 
                                                        }
                                                }                                                        
                                                if ($cols == 0)
                                                {
                                                       $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                                }
                                                $html .= '<td width="' . $width . '%"><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong><div class="gray">' . $htmlanswer . '</div></div><div style="padding-bottom:7px"></div></td>';
                                                $cols++;
                                                $num++;
                                                $c++;
                                                if ($cols == $columns)
                                                {
                                                        $html .= '</tr>';
                                                        $cols = 0;
                                                }
                                        }
                                }
                                if ($cols != $columns && $cols != 0)
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
                $html .= '</table>';
                if ($separator)
                {
                        $html .= '<div style="background-color:#cccccc;height:1px;width:100%;margin-top:12px;margin-bottom:12px"></div>';
                }
                return $html;
        }
        
        
        /**
        * Function to determine if a field can be displayed for the viewing category
        *
        * @param       integer       field id
        * @param       integer       category id
        *
        * @return      boolean       Returns true or false
        */
        function can_display_bid_field($fieldid = 0, $cid = 0)
        {
                // fetch categories this bid field is associated with
                $cids = $this->fetch_categories_assigned($fieldid, false);
                foreach ($cids AS $categoryid)
                {
                        if (isset($categoryid) AND $categoryid == $cid)
                        {
                                return true;
                        }
                }
                return false;
        }
        
        /**
        * Function to process the custom auction questions to be saved in the database
        *
        * @param       array         custom array
        * @param       integer       project id
        * @param       string        category mode (service or product)
        *
        * @return      null
        */
        function process_custom_bid_fields($custom = array(), $projectid = 0, $bidid = 0, $id = 0)
        {
                global $ilance;
                if ($ilance->categories->bidgrouping(fetch_auction('cid', $projectid)) == 0)
                {
                	$bidid = $id;
                }
                if (isset($custom) AND !empty($custom))
                {
                        foreach ($custom AS $questionid => $answerarray)
                        {
                                foreach ($answerarray AS $formname => $answer)
                                {
                                        $sql2 = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "bid_fields_answers
                                                WHERE fieldid = '" . intval($questionid) . "'
                                                    AND project_id = '" . intval($projectid) . "'
                                                    AND bid_id = '" . intval($bidid) . "'
                                        ", 0, null, __FILE__, __LINE__);                    
                                        if ($ilance->db->num_rows($sql2) > 0 AND !empty($answer))
                                        {
                                                if (is_array($answer))
                                                {
                                                        // multiple choice
                                                        $answer = serialize($answer);
                                                }
                                                
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "bid_fields_answers
                                                        SET answer = '" . $ilance->db->escape_string($answer) . "'
                                                        WHERE fieldid = '" . intval($questionid) . "'
                                                            AND project_id = '" . intval($projectid) . "'
                                                            AND bid_id = '" . intval($bidid) . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else
                                        {
                                                if (!empty($answer))
                                                {
                                                        if (is_array($answer))
                                                        {
                                                                // multiple choice
                                                                $answer = serialize($answer);
                                                        }
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "bid_fields_answers
                                                                (answerid, fieldid, project_id, bid_id, answer, date, visible)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($questionid) . "',
                                                                '" . intval($projectid) . "',
                                                                '" . intval($bidid) . "',
                                                                '" . $ilance->db->escape_string($answer) . "',
                                                                '" . DATETIME24H . "',
                                                                '1')
                                                        ", 0, null, __FILE__, __LINE__);    
                                                }
                                        }
                                }
                        }
                }
        }
        
        /**
        * Function to print all bid fields and checkboxes for updating and adding new categories in the AdminCP
        *
        * @param       integer       category id
        * @param       string        short language identifier (default eng)
        *
        * @return      string        Returns HTML representation of checkboxes as bid fields selectors
        */
        function print_bid_field_checkboxes($cid = 0, $slng = 'eng')
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                $html = '';
                $fields = array();
                $sql = $ilance->db->query("
                        SELECT fieldid, question_$slng AS question, inputtype
                        FROM " . DB_PREFIX . "bid_fields
                        WHERE visible = '1'
                        ORDER BY sort
                ", 0, null, __FILE__, __LINE__);                    
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $fields[$res['fieldid']] = $res['question'] . ' <span class="smaller gray">(' . $res['inputtype'] . ')</span>';
                        }
                }
                $sql = $ilance->db->query("
                        SELECT bidfields
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                ", 0, null, __FILE__, __LINE__);                    
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $bidfields = unserialize($res['bidfields']);
                }
                $i = 0;
                foreach ($fields AS $key => $title)
                {
                        if (!empty($bidfields) AND isset($bidfields[$i]) AND $key == $bidfields[$i])
                        {
                                $i++;
                                $html .= '<div><label for="bidfield_' . $key . '"><input id="bidfield_' . $key . '" type="checkbox" name="bidfieldtypes[]" value="' . $key . '" checked="checked" /> ' . $title . '</label></div>';
                        }
                        else
                        {
                                $html .= '<div><label for="bidfield_' . $key . '"><input id="bidfield_' . $key . '" type="checkbox" name="bidfieldtypes[]" value="' . $key . '" /> ' . $title . '</label></div>';
                        }
                }
                if (empty($html))
                {
                        $html .= '<a href="' . $ilpage['distribution'] . '?cmd=bids">' . '{_create_new_bid_fields}' . '</a>';
                }
                return $html;
        }
        
        /**
        * Function to print out how many categories are currently associated with the calling bid field id
        *
        * @param       integer       bid field id
        * @param       boolean       result in countonly? (default true)
        *
        * @return      string        Returns HTML representation of a count
        */
        function fetch_categories_assigned($fieldid = 0, $countonly = true)
        {
                global $ilance;
                $count = 0;
                $cids = array();
                $sql = $ilance->db->query("
                        SELECT cid, bidfields
                        FROM " . DB_PREFIX . "categories
                        WHERE bidfields != ''
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (is_serialized($res['bidfields']))
                                {
                                        $fields = unserialize($res['bidfields']);
                                        if (!empty($fields) AND is_array($fields))
                                        {
                                                foreach ($fields AS $fid)
                                                {
                                                        if (isset($fid) AND $fid == $fieldid)
                                                        {
                                                                $count++;
                                                                $cids[] = $res['cid'];
                                                        }
                                                }
                                        }
                                }
                        }
                }
                if ($countonly)
                {
                        return $count;
                }
                return $cids;
        }
        
        /**
        * Function to fetch the count of all answers submitted for a particular bid field id
        *
        * @param       integer       bid field id
        *
        * @return      string        Returns HTML representation of a count
        */
        function fetch_answer_count_submitted($fieldid = 0)
        {
                global $ilance;
                $count = 0;
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "bid_fields_answers
                        WHERE fieldid = '" . intval($fieldid) . "'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $count = $res['count'];
                }
                return $count;
        }
        
        /**
        * Function to fetch the count of all bid fields within a particular category
        *
        * @param       integer       category id
        *
        * @return      string        Returns HTML representation of a count
        */
        function print_bid_field_count_in_category($cid = 0)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                $fields = array();
                $bidfields = array();
                $i = 0;
                $sql = $ilance->db->query("
                        SELECT fieldid
                        FROM " . DB_PREFIX . "bid_fields
                        WHERE visible = '1'
                        ORDER BY sort
                ", 0, null, __FILE__, __LINE__);                    
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $fields[$res['fieldid']] = '';
                        }
                }
                $sql = $ilance->db->query("
                        SELECT bidfields
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                ", 0, null, __FILE__, __LINE__);                    
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if (is_serialized($res['bidfields']))
                        {
                                $bidfields = unserialize($res['bidfields']);
                        }
                }
                foreach ($fields AS $key)
                {
                        if (isset($bidfields[$i]))
                        {
                                $i++;  
                        }
                }
                return $i;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>