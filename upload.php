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
		'ajax'
	),
	'footer' => array()
);

// #### define top header nav ##################################################
$topnavlink = array(
	'upload'
);

// #### setup script location ##################################################
define('LOCATION', 'upload');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[upload]" => $ilcrumbs["$ilpage[upload]"]);
if (empty($_SESSION['ilancedata']['user']['userid']) OR (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] <= 0))
{
	$area_title = '{_access_denied_menu_please_login}';
        $page_title = SITE_NAME . ' - {_access_denied_menu_please_login}';
	$ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_denied.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time') );
	$ilance->template->pprint('popupmain', array('input_style'));
	$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
	exit();
}
$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
$user_id = (isset($uncrypted['user_id']) AND is_numeric($uncrypted['user_id'])) ? $uncrypted['user_id'] : $_SESSION['ilancedata']['user']['userid'];
$crypted = $ilance->GPC['crypted'];
$removeattachments = isset($ilance->GPC['removeattachments']) ? 1 : 0;
$attachments = isset($ilance->GPC['attachments']) ? $ilance->GPC['attachments'] : '';
$attach_user_max = print_filesize($ilance->permissions->check_access($user_id, 'attachlimit'));
$attachmentlistobj = !empty($uncrypted['attachmentlist']) ? $uncrypted['attachmentlist'] : 'attachmentlist';
$attachmentlistobj_hide = !empty($uncrypted['attachmentlist_hide']) ? $uncrypted['attachmentlist_hide'] : '';
$uncrypted['ads_id'] = isset($uncrypted['ads_id']) ? $uncrypted['ads_id'] : '';
$attach_usage_total = $attach_usage_left = 0;
if (isset($uncrypted['attachtype']) AND $uncrypted['attachtype'] == 'portfolio')
{
	if (empty($uncrypted['cid']))
	{
		$uncrypted['cid'] = 0;
	}
	$cid = isset($uncrypted['cid']) ? intval($uncrypted['cid']) : 0;
	$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 1, true);
	$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown(0, 'service', 'level', 'cid', 0, $_SESSION['ilancedata']['user']['slng'], 1, '', 1, 1, 0, '540px', 0, 1, 0, false, false, $ilance->categories->cats);
}
$sql_file_sum = $ilance->db->query("
	SELECT SUM(filesize) AS attach_usage_total
	FROM " . DB_PREFIX . "attachment
	WHERE user_id = '" . intval($user_id) . "'
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
$attach_usage_left = ($ilance->permissions->check_access($user_id, 'attachlimit') - $res_file_sum['attach_usage_total']);
$attach_usage_left = ($attach_usage_left <= 0) ? print_filesize(0) : print_filesize($attach_usage_left);
// #### REMOVE ATTACHMENTS #############################################
if (isset($removeattachments) AND $removeattachments AND isset($uncrypted['attachtype']))
{
	$area_title = '{_removing_file_attachments}';
	$page_title = SITE_NAME . ' - {_removing_file_attachments}';
	$remote_addr = IPADDRESS;
	$referer = REFERRER;
	$error_message = '';
	$show['error'] = $show['notice'] = false;
	if (isset($attachments) AND is_array($attachments) AND count($attachments) > 0)
	{
		foreach ($attachments AS $value)
		{
			if ($ilance->db->fetch_field(DB_PREFIX . "attachment", "attachid = '" . intval($value) . "'", "portfolio_id") > 0)
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "portfolio
					WHERE portfolio_id = '" . $ilance->db->fetch_field(DB_PREFIX . "attachment", "attachid = '" . intval($value) . "'", "portfolio_id") . "'
						AND user_id = '" . $user_id . "'
					LIMIT 1
				");
			}
			$ilance->attachment->remove_attachment(intval($value), $user_id);
			
			($apihook = $ilance->api('upload_remove_attachments_foreach_end')) ? eval($apihook) : false;
		}
	}
	else
	{
		$show['error'] = true;
		$error_message = '{_you_did_not_select_any_attachments_to_remove_operation_aborted}';
	}
	// #### rebuild attachment select list #########################
	$sql_attachments = array();
	$uncrypted['project_id'] = isset($uncrypted['project_id']) ? $uncrypted['project_id'] : 0;
	$uncrypted['user_id'] = isset($uncrypted['user_id']) ? $uncrypted['user_id'] : 0;
	$uncrypted['filehash'] = isset($uncrypted['filehash']) ? $uncrypted['filehash'] : '';
	$condition = $ilance->attachment->handle_attachtype_rebuild_settings($uncrypted['attachtype'], $uncrypted['user_id'], $uncrypted['project_id'], $uncrypted['filehash']);
	$maximum_files = $condition['maximum_files'];
	$max_width = $condition['max_width'];
	$max_height = $condition['max_height'];
	$max_filesize = $condition['max_filesize'];
	$max_size = $condition['max_size'];
	$extensions = $condition['extensions'];
	$query = $condition['query'];
	// #### check if the total attachment limit permission has exceeded for this members upload
	$futuresize = ($res_file_sum['attach_usage_total']);
	$totalpercentage = round(($futuresize / $ilance->permissions->check_access($user_id, 'attachlimit')) * 100);
	if ($totalpercentage > 100)
	{
		$show['error'] = true;
		$error_message .= '<div>{_sorry_you_do_not_have_enough_space_to_upload_new_files}</div>';        
	}
	unset($condition);
	$attachment_list_html = '<table cellpadding="3" cellspacing="0"><tr valign="middle">';
	$attachment_list = '<select multiple name="attachments[]" size="5" style="overflow:hidden;width:420px" class="smaller">';
	$sql_attachments = $ilance->db->query($query);
	if ($ilance->db->num_rows($sql_attachments) >= $maximum_files)
	{
		$upload_style = 'disabled="disabled"';
	}
	while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
	{            
		$moderated = '';
		if ($res['visible'] == 0)
		{
			$moderated = ' ({_review_in_progress})';
		}
		// image
		if ($res['width'] > 0 OR $res['height'] > 0 OR $res['width_original'] > 0 OR $res['height_original'] > 0)
		{
			$attachment_list_html .= '<td style="border:1px solid #ECECEC"><div title="' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . $moderated . '"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $res['filehash'] . '" border="0" alt="" id="" /></div></td>';
			$attachment_list .= '<option value="' . $res['attachid'] . '">' . handle_input_keywords($res['filename']) . ($res['width'] > 0 ? ' (' . $res['width'] . 'px x ' . $res['height'] . 'px)' : '') . ' (' . print_filesize($res['filesize']) . ')' . $moderated . '</option>';
		}
		// non image
		else
		{
			$attachment_list_html .= '<td style="border:1px solid #ECECEC"><div class="smaller" title="' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . $moderated . '">' . handle_input_keywords($res['filename']) . '</div></td>';
			$attachment_list .= '<option value="' . $res['attachid'] . '">' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . ')' . $moderated . '</option>';
		}
	}
	$attachment_list_html .= '</tr></table>';
	$attachment_list .= '</select>';
	$js = $ilance->attachment->print_innerhtml_js($attachmentlistobj, $attachment_list_html, $attachmentlistobj_hide);
	$headinclude .= '<script type="text/javascript">
<!--
';
	// #### PORTFOLIO UPLOAD JAVASCRIPT ############################
	if ($uncrypted['attachtype'] == 'portfolio')
	{
		$headinclude .= 'function check_upload(formobj)
{
        var haveupload = false;
        var havecaption = false;
        var havedescription = false;
        var havecid = false;
        for (var i = 0; i < formobj.elements.length; i++)
        {
                var elm = formobj.elements[i];
                if (elm.type == \'file\')
                {
                        if (elm.value != \'\')
                        {
                                haveupload = true;
                        }
                }
                if (elm.name == \'cid\')
                {
                        if (elm.value != \'0\')
                        {
                                havecid = true;
                        }
                }
                if (elm.name == \'caption\')
                {
                        if (elm.value != \'\')
                        {
                                havecaption = true;
                        }
                }
                if (elm.name == \'description\')
                {
                        if (elm.value != \'\')
                        {
                                havedescription = true;
                        }
                }
        }
        if (!havecid)
        {
                alert_js(phrase[\'_please_select_a_portfolio_category_to_upload_this_attachment\']);
                return false;
        }
        if (!havecaption)
        {
                alert_js(phrase[\'_please_enter_a_caption_name_to_upload_this_attachment\']);
                return false;
        }
        if (!havedescription)
        {
                alert_js(phrase[\'_please_enter_a_description_to_upload_this_attachment\']);
                return false;
        }
        if (haveupload)
        {
                toggle_id(\'uploading\');
                return true;
        }
        else
        {
                alert_js(phrase[\'_please_use_the_browse_button_to_attach_media\']);
                return false;
        }
}';	
	}
	// #### UPLOAD JAVASCRIPT ######################################
	else 
	{
		$headinclude .= 'function check_upload(formobj)
{
        var haveupload = false;
        for (var i = 0; i < formobj.elements.length; i++)
        {
                var elm = formobj.elements[i];
                if (elm.type == \'file\')
                {
                        if (elm.value != \'\')
                        {
                                haveupload = true;
                        }
                }
        }
        if (haveupload)
        {
                toggle_id(\'uploading\');
                return true;
        }
        else
        {
                alert_js(phrase[\'_please_use_the_browse_button_to_attach_media\']);
                return false;
        }
}';	
	}
	$headinclude .= '
//-->
</script>';
	// detect if we need to refresh the parent menu after this popup attachment window closes
	// this is useful for places like portfolio menu where the user uploads new images
	$onbeforeunload = '';
	$closeonclick = 'window.close();';
	if (isset($ilance->GPC['refresh']) AND $ilance->GPC['refresh'])
	{
		$closeonclick = 'close_popup_window();';
	}
	$ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_upload_attachment.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw') );
	$ilance->template->pprint('popupmain', array('closeonclick','crypted','js','category_pulldown','attach_usage_total','attach_usage_left','extensions','max_size','max_width','max_height','error_message','notice_message','maximum_files','attach_user_max','upload_style','pmb_id','attachment_list','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','input_style'));
	$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
	exit();
}
// #### BEGIN FILE UPLOAD ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-upload-now' AND isset($uncrypted['attachtype']))
{
	$area_title = '{_uploading_attachment_in_progress}';
	$page_title = SITE_NAME . ' - {_uploading_attachment_in_progress}';
	$upload_to = $notice_message = $error_message = $upload_style = $moderated = '';
	$condition = $ilance->attachment->handle_attachtype_upload_settings($uncrypted['attachtype']);
	$max_filesize = $condition['max_filesize'];
	$max_size = $condition['max_size'];
	$upload_to = $condition['upload_to'];
	$extensions = $condition['extensions'];                
	unset($condition);
	// #### PROCESS AND SAVE ATTACHMENT ############################
	$ilance->attachment->temp_file_name = trim($_FILES['upload']['tmp_name']);
	$ilance->attachment->file_name = $_FILES['upload']['name'];
	$ilance->attachment->filetype = $_FILES['upload']['type'];
	$ilance->attachment->upload_dir = $upload_to;
	$ilance->attachment->max_file_size = $max_filesize;
	$ilance->attachment->attachtype = isset($uncrypted['attachtype']) ? $uncrypted['attachtype'] : '';
	// #### validation #############################################
	$valid_ext = $ilance->attachment->validate_extension();
	$file_size = $ilance->attachment->get_file_size();
	$valid_size = $ilance->attachment->validate_size();
	$file_type = $ilance->attachment->get_file_type();
	// #### check if the total attachment limit permission has exceeded for this members upload
	$futuresize = ($res_file_sum['attach_usage_total'] + $file_size);
	$totalpercentage = round(($futuresize / $ilance->permissions->check_access($user_id, 'attachlimit')) * 100);
	// #### set upload error defaults ##############################
	$show['error'] = $show['notice'] = false;
	// #### rebuild attachment select list #########################
	$sql_attachments = array();
	$uncrypted['project_id'] = isset($uncrypted['project_id']) ? $uncrypted['project_id'] : 0;
	$uncrypted['user_id'] = isset($uncrypted['user_id']) ? $uncrypted['user_id'] : 0;
	$uncrypted['filehash'] = isset($uncrypted['filehash']) ? $uncrypted['filehash'] : '';
	$uncrypted['ads_id'] = isset($uncrypted['ads_id']) ? $uncrypted['ads_id'] : '';
	$condition = $ilance->attachment->handle_attachtype_rebuild_settings($uncrypted['attachtype'], $uncrypted['user_id'], $uncrypted['project_id'], $uncrypted['filehash'], $uncrypted['ads_id']);
	$maximum_files = $condition['maximum_files'];
	$max_width = $condition['max_width'];
	$max_height = $condition['max_height'];
	$max_filesize = $condition['max_filesize'];
	$max_size = $condition['max_size'];
	$extensions = $condition['extensions'];
	$query = $condition['query'];                
	unset($condition);
	// #### have we exceeded our account space based on this upload?
	if ($totalpercentage > 100)
	{
		$error_message .= '<div>{_sorry_you_do_not_have_enough_space_to_upload_new_files}</div>';        
	}
	// #### is the filename extension valid?
	if ($valid_ext == false)
	{
		$error_message .= '<div>{_the_file_extension_is_invalid_or_the_width_slash_height_exceeds_our_limits}</div>';
	}
	// #### is the width or height bad?
	if ((!empty($valid_size['failedwidth']) AND $valid_size['failedwidth'] OR !empty($valid_size['failedheight']) AND $valid_size['failedheight']) AND !isset($uncrypted['ads_id']))
	{
		$error_message .= '<div>{_maximum_width} <strong>' . $max_width . 'px</strong>, {_maximum_height} <strong>' . $max_height . 'px</strong>, {_maximum_filesize}: <strong>' . $max_size . '</strong> {_and_your_attachment_was} <strong>' . $valid_size['uploadwidth'] . 'px</strong> ' . mb_strtolower('{_by}') . ' <strong>' . $valid_size['uploadheight'] . 'px</strong>, {_upload_size}: <strong>' . print_filesize($valid_size['uploadfilesize']) . '</strong></div>';
	}
	// #### is the file size bad?
	if (!empty($valid_size['failedfilesize']) AND $valid_size['failedfilesize'])
	{
		$error_message .= '<div>{_the_file_size_is_invalid_please_try_again_the_maximum_file_size_is}: <strong>' . $max_size . '</strong> {_and_your_file_was}: <strong>' . print_filesize($file_size) . '</strong></div>';
	}
	if (!empty($error_message))
	{
		// we have upload errors so set our upload $error to true
		$show['error'] = true;
	}
	// #### begin upload ###########################################
	if ($show['error'] == false)
	{
		if ($ilance->attachment->save_attachment($valid_size))
		{
			// no error messages detected .. save the attachment
			$area_title = '{_attachment_uploaded}';
			$page_title = SITE_NAME . ' - {_attachment_uploaded}';
			if ($ilconfig['attachment_moderationdisabled'] == 0)
			{
				$show['notice'] = true;
				$notice_message .= '{_attachments_are_currently_being_moderated_by_our_staff}';
			}
			$details = 'File name: ' . $_FILES['upload']['name'] . ', File type: ' . $file_type . ', File size: ' . $valid_size['uploadfilesize'] . ', Height: ' . $valid_size['uploadheight'] . ', Width: ' . $valid_size['uploadwidth'] . ', Attach type: ' . $uncrypted['attachtype'];
			log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['upload'], $uncrypted['attachtype'], $uncrypted['project_id'], $details) ;
		}
		else
		{
			// errors detected .. do not save the attachment
			$show['error'] = true;
			$area_title = '{_attachment_could_not_be_saved}';
			$page_title = SITE_NAME . ' - {_attachment_could_not_be_saved}';
			$error_message .= '{_your_attachment_could_not_be_uploaded}';
			log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['upload'], $uncrypted['attachtype'], $uncrypted['project_id'], $error_message);
		}
	}
	$attachment_list_html = '<table cellpadding="3" cellspacing="0"><tr valign="middle">';
	$attachment_list = '<select multiple name="attachments[]" size="5" style="overflow:hidden;width:420px" class="smaller">';
	$sql_attachments = $ilance->db->query($query);
	if ($ilance->db->num_rows($sql_attachments) >= $maximum_files)
	{
		$upload_style = 'disabled="disabled"';
	}
	$image_number = 0;
	while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
	{
		$moderated = '';
		if ($res['visible'] == 0)
		{
			$moderated = ' ({_review_in_progress})';
		}
		// image
		if ($res['width'] > 0 OR $res['height'] > 0 OR $res['width_original'] > 0 OR $res['height_original'] > 0)
		{
			$attachment_list_html .= '<td style="border:1px solid #ECECEC"><div title="' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . $moderated . '"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $res['filehash'] . '" border="0" alt="" id="" /></div></td>';
			$attachment_list .= '<option value="' . $res['attachid'] . '">' . handle_input_keywords($res['filename']) . ($res['width'] > 0 ? ' (' . $res['width'] . 'px x ' . $res['height'] . 'px)' : '') . ' (' . print_filesize($res['filesize']) . ')' . $moderated . '</option>';
		}
		// non image
		else
		{
			$attachment_list_html .= '<td style="border:1px solid #ECECEC"><div class="smaller" title="' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . $moderated . '">' . handle_input_keywords($res['filename']) . '</div></td>';
			$attachment_list .= '<option value="' . $res['attachid'] . '">' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . ')' . $moderated . '</option>';
		}
		++$image_number;
	}
	$attachment_list_html .= '</tr></table>';
	$attachment_list .= '</select>';
	$js = $ilance->attachment->print_innerhtml_js($attachmentlistobj, $attachment_list_html, $attachmentlistobj_hide);
	$headinclude .= '<script type="text/javascript">
<!--
';
	if ($uncrypted['attachtype'] == 'portfolio')
	{
		$headinclude .= 'function check_upload(formobj)
{
	var haveupload = false;
	var havecaption = false;
	var havedescription = false;
	var havecid = false;
	for (var i = 0; i < formobj.elements.length; i++)
	{
		var elm = formobj.elements[i];
		if (elm.type == \'file\')
		{
			if (elm.value != \'\')
			{
				haveupload = true;
			}
		}
		if (elm.name == \'cid\')
		{
			if (elm.value != \'0\')
			{
				havecid = true;
			}
		}
		if (elm.name == \'caption\')
		{
			if (elm.value != \'\')
			{
				havecaption = true;
			}
		}
		if (elm.name == \'description\')
		{
			if (elm.value != \'\')
			{
				havedescription = true;
			}
		}
	}
	if (!havecid)
	{
		alert_js(phrase[\'_please_select_a_portfolio_category_to_upload_this_attachment\']);
		return false;
	}
	if (!havecaption)
	{
		alert_js(phrase[\'_please_enter_a_caption_name_to_upload_this_attachment\']);
		return false;
	}
	if (!havedescription)
	{
		alert_js(phrase[\'_please_enter_a_description_to_upload_this_attachment\']);
		return false;
	}
	if (haveupload)
	{
		toggle_id(\'uploading\');
		return true;
	}
	else
	{
		alert_js(phrase[\'_please_use_the_browse_button_to_attach_media\']);
		return false;
	}
}';	
	}
	else 
	{
		$headinclude .= 'function check_upload(formobj)
{
	var haveupload = false;
	for (var i = 0; i < formobj.elements.length; i++)
	{
		var elm = formobj.elements[i];
		if (elm.type == \'file\')
		{
			if (elm.value != \'\')
			{
				haveupload = true;
			}
		}
	}
	if (haveupload)
	{
		toggle_id(\'uploading\');
		return true;
	}
	else
	{
		alert_js(phrase[\'_please_use_the_browse_button_to_attach_media\']);
		return false;
	}
}';	
	}
	$headinclude .= '
//-->
</script>
';
	// detect if we need to refresh the parent menu after this popup attachment window closes
	// this is useful for places like portfolio menu where the user uploads new images
	$onbeforeunload = '';
	$closeonclick = 'window.close();';
	if (isset($ilance->GPC['refresh']) AND $ilance->GPC['refresh'])
	{
		$closeonclick = 'close_popup_window();';
	}
	$ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_upload_attachment.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw') );
	$ilance->template->pprint('popupmain', array('closeonclick','crypted','js','category_pulldown','attach_usage_total','attach_usage_left','extensions','max_size','max_width','max_height','error_message','notice_message','maximum_files','attach_user_max','upload_style','pmb_id','attachment_list','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','input_style'));
	$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
	exit();
}
// #### POPUP LANDING PAGE #############################################
else
{
	if (empty($uncrypted))
	{
		$area_title = '{_access_denied_menu_please_login}';
		$page_title = SITE_NAME . ' - {_access_denied_menu_please_login}';
		$ilance->template->load_popup('popupheader', 'popup_header.html');
		$ilance->template->load_popup('popupmain', 'popup_denied.html');
		$ilance->template->load_popup('popupfooter', 'popup_footer.html');
		$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('popupheader');
		$ilance->template->parse_if_blocks('popupmain');
		$ilance->template->parse_if_blocks('popupfooter');
		$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time') );
		$ilance->template->pprint('popupmain', array('input_style'));
		$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
		exit();
	}
	$area_title = '{_uploading_file_attachments}';
	$page_title = SITE_NAME . ' - {_uploading_file_attachments}';
	$remote_addr = IPADDRESS;
	$referer = REFERRER;
	$error_message = '';
	// #### rebuild attachment select list #########################
	$sql_attachments = array();
	$uncrypted['project_id'] = isset($uncrypted['project_id']) ? $uncrypted['project_id'] : 0;
	$uncrypted['user_id'] = isset($uncrypted['user_id']) ? $uncrypted['user_id'] : 0;
	$uncrypted['filehash'] = isset($uncrypted['filehash']) ? $uncrypted['filehash'] : '';
	$uncrypted['ads_id'] = isset($uncrypted['ads_id']) ? $uncrypted['ads_id'] : '';
	$condition = $ilance->attachment->handle_attachtype_rebuild_settings($uncrypted['attachtype'], $uncrypted['user_id'], $uncrypted['project_id'], $uncrypted['filehash'], $uncrypted['ads_id']);
	$maximum_files = $condition['maximum_files'];
	$max_width = $condition['max_width'];
	$max_height = $condition['max_height'];
	$max_filesize = $condition['max_filesize'];
	$max_size = $condition['max_size'];
	$extensions = $condition['extensions'];
	$query = $condition['query'];
	unset($condition);
	// #### check if the total attachment limit permission has exceeded for this members upload
	$futuresize = ($res_file_sum['attach_usage_total']);
	$totalpercentage = 100;
	if (intval($ilance->permissions->check_access($user_id, 'attachlimit')) > 0)
	{
		$totalpercentage = round(($futuresize / $ilance->permissions->check_access($user_id, 'attachlimit')) * 100);
	}
	if ($totalpercentage >= 100)
	{
		$error_message .= '<div>{_sorry_you_do_not_have_enough_space_to_upload_new_files}</div>';        
	}
	if (!empty($error_message))
	{
		$show['error'] = true;
	}
	$attachment_list_html = '<table cellpadding="3" cellspacing="0"><tr valign="middle">';
	$attachment_list = '<select multiple name="attachments[]" size="5" style="overflow:hidden;width:420px" class="smaller">';
	$sql_attachments = $ilance->db->query($query);
	$upload_style = ($ilance->db->num_rows($sql_attachments) >= $maximum_files) ? 'disabled="disabled"' : '';
	$image_number = 0;
	while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
	{
		$moderated = '';
		if ($res['visible'] == 0)
		{
			$moderated = ' ({_review_in_progress})';
		}
		// image
		if ($res['width'] > 0 OR $res['height'] > 0 OR $res['width_original'] > 0 OR $res['height_original'] > 0)
		{
			$attachment_list_html .= '<td style="border:1px solid #ECECEC"><div title="' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . $moderated . '"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $res['filehash'] . '" border="0" alt="" id="" /></div></td>';
			$attachment_list .= '<option value="' . $res['attachid'] . '">' . handle_input_keywords($res['filename']) . ($res['width'] > 0 ? ' (' . $res['width'] . 'px x ' . $res['height'] . 'px)' : '') . ' (' . print_filesize($res['filesize']) . ')' . $moderated . '</option>';
		}
		// non image
		else
		{
			$attachment_list_html .= '<td style="border:1px solid #ECECEC"><div class="smaller" title="' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . $moderated . '">' . handle_input_keywords($res['filename']) . '</div></td>';
			$attachment_list .= '<option value="' . $res['attachid'] . '">' . handle_input_keywords($res['filename']) . ' (' . print_filesize($res['filesize']) . ')' . $moderated . '</option>';
		}
		++$image_number; 
	}
	$attachment_list_html .= '</tr></table>';
	$attachment_list .= '</select>';
	$js = $ilance->attachment->print_innerhtml_js($attachmentlistobj, $attachment_list_html, $attachmentlistobj_hide);
	$headinclude .= '<script type="text/javascript">
<!--
';
	if ($uncrypted['attachtype'] == 'portfolio')
	{
		$headinclude .= 'function check_upload(formobj)
{
        var haveupload = false;
        var havecaption = false;
        var havedescription = false;
        var havecid = false;
        for (var i = 0; i < formobj.elements.length; i++)
        {
                var elm = formobj.elements[i];
                if (elm.type == \'file\')
                {
                        if (elm.value != \'\')
                        {
                                haveupload = true;
                        }
                }			
                if (elm.name == \'cid\')
                {
                        if (elm.value != \'0\')
                        {
                                havecid = true;
                        }
                }			
                if (elm.name == \'caption\')
                {
                        if (elm.value != \'\')
                        {
                                havecaption = true;
                        }
                }			
                if (elm.name == \'description\')
                {
                        if (elm.value != \'\')
                        {
                                havedescription = true;
                        }
                }
        }		
        if (!havecid)
        {
                alert_js(phrase[\'_please_select_a_portfolio_category_to_upload_this_attachment\']);
                return false;
        }
        if (!havecaption)
        {
                alert_js(phrase[\'_please_enter_a_caption_name_to_upload_this_attachment\']);
                return false;
        }
        if (!havedescription)
        {
                alert_js(phrase[\'_please_enter_a_description_to_upload_this_attachment\']);
                return false;
        }		
        if (haveupload)
        {
                toggle_id("uploading");
                return true;
        }
        else
        {
                alert_js(phrase[\'_please_use_the_browse_button_to_attach_media\']);
                return false;
        }
}';	
	}
	else 
	{
		$headinclude .= 'function check_upload(formobj)
{
	var haveupload = false;
	for (var i = 0; i < formobj.elements.length; i++)
	{
		var elm = formobj.elements[i];
		if (elm.type == \'file\')
		{
			if (elm.value != \'\')
			{
				haveupload = true;
			}
		}
	}		
	if (haveupload)
	{
		toggle_id(\'uploading\');
		return true;
	}
	else
	{
		alert_js(phrase[\'_please_use_the_browse_button_to_attach_media\']);
		return false;
	}
}';	
	}
	$headinclude .= '
//-->
</script>
';
	// detect if we need to refresh the parent menu after this popup attachment window closes
	// this is useful for places like portfolio menu where the user uploads new images
	$onbeforeunload = '';
	$closeonclick = 'window.close();';
	if (isset($ilance->GPC['refresh']) AND $ilance->GPC['refresh'])
	{
		$closeonclick = 'close_popup_window();';
	}
	$ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_upload_attachment.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw'));
	$ilance->template->pprint('popupmain', array('closeonclick','crypted','js','category_pulldown','attach_usage_total','attach_usage_left','extensions','max_size','max_width','max_height','error_message','notice_message','maximum_files','attach_user_max','upload_style','pmb_id','attachment_list','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','input_style'));
	$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>