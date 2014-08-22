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
* Core WYSIWYG functions for iLance
*
* @package      iLance\Global\WYSIWYG
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to print the WYSIWYG / BBcode editor
*
* @param       string         field name
* @param       string         message
* @param       string         wysiwyg editor instance id
* @param       boolean        enable wysiwyg?
* @param       boolean        show switch mode (bbedit to wysiwyg) button?
* @param       boolean        is html? (default false)
* @param       string         width of wysiwyg editor
* @param       string         height of wysiwyg editor
* @param       string         javascript if applicable
* @param       string         editor load type (bbeditor/ckeditor)
* @param       string         toolbar configuration
* @param       integer        tabindex
*
* @return      string         Returns usernames separated by a line break
*/
function print_wysiwyg_editor($fieldname = '', $text = '', $instanceid = 'bbeditor', $enablewysiwyg = 1, $showswitchmode = 1, $ishtml = false, $width = '595', $height = '250', $js = '', $type = 'bbeditor', $toolbar = '', $tabindex = 2)
{
        global $ilance, $ilconfig, $phrase, $headinclude, $show;
        $html = '';
        if ($type == 'bbeditor')
        {
	        $cssclass = 'ilance_wysiwyg';
		$show['footerwysiwygpopup'] = true;
		if (isset($text))
		{
			$text = htmlspecialchars_uni($text);
		}
		$html = '<div class="' . $cssclass . '"><textarea style="visibility:hidden;position:absolute;top:0;left:0;" name="' . $fieldname . '" id="' . $fieldname . '_id" rows="1" cols="1">' . $text . '</textarea>';
		$html .= '
		    <script type="text/javascript">
<!--
';
		if ($instanceid == 'bbeditor')
		{
			$html .= 'function fetch_bbeditor_data()
{
	prepare_bbeditor_wysiwygs(\'' . (int)$enablewysiwyg . '\');            
	var bbcode_output = fetch_js_object(\'bbeditor_bbcode_ouput_' . $instanceid . '\').value;
	fetch_js_object(\'' . $fieldname . '_id\').value = bbcode_output;
}
';
		}
		else
		{
			$html .= 'function fetch_bbeditor_data_' . $instanceid . '()
{
        prepare_bbeditor_wysiwygs(\'' . (int)$enablewysiwyg . '\');
        var bbcode_output = fetch_js_object(\'bbeditor_bbcode_ouput_' . $instanceid . '\').value;
        fetch_js_object(\'' . $fieldname . '_id\').value = bbcode_output;
}
';
		}
		$html .= 'var bbcodetext = fetch_js_object(\'' . $fieldname . '_id\').value;
print_wysiwyg_editor(\'max\', \'' . $instanceid . '\', bbcodetext, \'100%\', \'' . $height . 'px\', \'' . $width . 'px\', \'' . $js . '\', \'' . (int)$showswitchmode . '\', \'' . (int)$showswitchmode . '\', \'' . (int)$enablewysiwyg . '\', \'' . (int)$tabindex . '\');
//-->
</script></div>';
        }
        else if ($type == 'ckeditor')
        {
        	$config_toolbar = "";
        	if (!empty($toolbar))
        	{
        		$config_toolbar = "toolbar :
[
	" . remove_newline($toolbar) . "
],";
        	}
        	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : fetch_site_slng();
        	$slng = mb_substr($slng, 0, 2);
    		if (isset($ilance->GPC['action']) AND ($ilance->GPC['action'] == 'new-item' OR $ilance->GPC['action'] == 'update-item') OR isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'homepage')
		{
			$config = "
language : '$slng',
enterMode : CKEDITOR.ENTER_BR,
shiftEnterMode : CKEDITOR.ENTER_P,
$config_toolbar
width : 'auto',
height : $height
";
		}
		else
		{
			$config = "
language : '$slng',
enterMode : CKEDITOR.ENTER_BR,
shiftEnterMode : CKEDITOR.ENTER_P,
$config_toolbar
width : $width,
height : $height
			";
		}
        	$html = '<textarea name="' . $fieldname . '" id="' . $fieldname . '_id" tabindex="' . $tabindex . '">' . $text . '</textarea>
<script type="text/javascript">
<!--
if (typeof CKEDITOR != \'undefined\')
{
	var editor = CKEDITOR.replace(\'' . $fieldname . '_id\'' . (!empty($config) ? ', {' . $config . '}' : '') . ');
}
//-->
</script>
';
        }
        
        ($apihook = $ilance->api('print_wysiwyg_editor_end')) ? eval($apihook) : false;
    
        return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>